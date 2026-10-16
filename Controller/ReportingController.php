<?php

/**
 * Serves Reporting views and enforces the write boundary for budget data.
 *
 * Related test file: tests/ControllerContractTest.php.
 */

namespace Kanboard\Plugin\Reporting\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Core\Controller\AccessForbiddenException;
use Kanboard\Core\Controller\PageNotFoundException;

class ReportingController extends BaseController
{
    const PLUGIN_NAME = 'Reporting';

    /**
     * Display the current user's project budgets.
     */
    public function ReportingBudget()
    {
        $this->renderDashboard('Reporting:reporting/budget', t('Budget overview'));
    }

    /**
     * Display usage entries grouped by budgeted project.
     */
    public function ReportingUsed()
    {
        $this->renderDashboard('Reporting:reporting/used', t('Budget usage'));
    }

    /**
     * Display a printable summary of the current user's reporting data.
     */
    public function ReportingReport()
    {
        $this->renderDashboard('Reporting:reporting/report', t('Budget report'));
    }

    /**
     * Display the create-budget modal.
     *
     * @param array $values
     * @param array $errors
     */
    public function ShowAddBudget(array $values = array(), array $errors = array())
    {
        $includeProjectId = isset($values['project_id']) ? (int) $values['project_id'] : 0;
        $projects = $this->getAvailableBudgetProjects($includeProjectId);

        if (empty($values)) {
            $values = array('date' => date('Y-m-d'));
        }

        $this->response->html($this->template->render('Reporting:reporting/budget_form', array(
            'action' => 'AddBudget',
            'errors' => $errors,
            'projects' => $this->getProjectOptions($projects),
            'submitLabel' => t('Create budget'),
            'title' => t('Create budget'),
            'values' => $values,
        )));
    }

    /**
     * Validate and create a budget owned by the signed-in user.
     */
    public function AddBudget()
    {
        $this->assertPostWithValidToken();
        $values = $this->getExpectedValues(array('project_id', 'budget', 'comment', 'date'));
        list($valid, $values, $errors) = $this->reportingModel->validateBudget($values);

        if ($valid) {
            $this->assertProjectAccess($values['project_id']);

            if ($this->reportingModel->createBudget($this->userSession->getId(), $values)) {
                $this->flash->success(t('Budget created successfully.'));
                return $this->redirectTo('ReportingBudget');
            }

            $errors['project_id'][] = t('You already have a budget for this project.');
        }

        $this->ShowAddBudget($values, $errors);
    }

    /**
     * Display the edit-budget modal for one user-owned row.
     */
    public function ShowEditBudget()
    {
        $budget = $this->getOwnedBudget();
        $project = $this->assertProjectAccess($budget['project_id']);
        $budget['project_name'] = $project['name'];

        $this->response->html($this->template->render('Reporting:reporting/budget_form', array(
            'action' => 'EditBudget',
            'errors' => array(),
            'projects' => array(),
            'submitLabel' => t('Save budget'),
            'title' => t('Edit budget'),
            'values' => $budget,
        )));
    }

    /**
     * Validate and update a budget without allowing ownership reassignment.
     */
    public function EditBudget()
    {
        $this->assertPostWithValidToken();
        $values = $this->getExpectedValues(array('id', 'budget', 'comment', 'date'));
        $budget = $this->getOwnedBudget(isset($values['id']) ? (int) $values['id'] : 0);
        $project = $this->assertProjectAccess($budget['project_id']);
        $values['project_id'] = (int) $budget['project_id'];
        list($valid, $values, $errors) = $this->reportingModel->validateBudget($values);

        if ($valid && $this->reportingModel->updateBudget($budget['id'], $this->userSession->getId(), $values)) {
            $this->flash->success(t('Budget updated successfully.'));
            return $this->redirectTo('ReportingBudget');
        }

        if ($valid) {
            $errors['budget'][] = t('Unable to update this budget.');
        }

        $values['id'] = $budget['id'];
        $values['project_name'] = $project['name'];
        $this->response->html($this->template->render('Reporting:reporting/budget_form', array(
            'action' => 'EditBudget',
            'errors' => $errors,
            'projects' => array(),
            'submitLabel' => t('Save budget'),
            'title' => t('Edit budget'),
            'values' => $values,
        )));
    }

    /**
     * Display a destructive-action confirmation before deleting a budget.
     */
    public function ConfirmDeleteBudget()
    {
        $budget = $this->getOwnedBudget();
        $this->assertProjectAccess($budget['project_id']);

        $this->response->html($this->template->render('Reporting:reporting/budget_remove', array(
            'budget' => $budget,
        )));
    }

    /**
     * Delete a budget and its usage entries within the current user's scope.
     */
    public function DeleteBudget()
    {
        $this->assertPostWithValidToken();
        $values = $this->getExpectedValues(array('id'));
        $budget = $this->getOwnedBudget(isset($values['id']) ? (int) $values['id'] : 0);
        $this->assertProjectAccess($budget['project_id']);

        if ($this->reportingModel->removeBudget($budget['id'], $this->userSession->getId())) {
            $this->flash->success(t('Budget and its usage entries removed successfully.'));
        } else {
            $this->flash->failure(t('Unable to remove this budget.'));
        }

        $this->redirectTo('ReportingBudget');
    }

    /**
     * Display the add-usage modal for an accessible budgeted project.
     *
     * @param array $values
     * @param array $errors
     */
    public function ShowAddUsed(array $values = array(), array $errors = array())
    {
        $projectId = isset($values['project_id'])
            ? (int) $values['project_id']
            : $this->request->getIntegerParam('project_id');
        $project = $this->assertProjectAccess($projectId);
        $budget = $this->reportingModel->getBudgetByProject($this->userSession->getId(), $projectId);

        if (empty($budget)) {
            throw new PageNotFoundException();
        }

        if (empty($values)) {
            $values = array('project_id' => $projectId, 'date' => date('Y-m-d'));
        }

        $this->response->html($this->template->render('Reporting:reporting/usage_form', array(
            'budget' => $budget,
            'errors' => $errors,
            'project' => $project,
            'values' => $values,
        )));
    }

    /**
     * Validate and create a usage entry owned by the signed-in user.
     */
    public function AddUsed()
    {
        $this->assertPostWithValidToken();
        $values = $this->getExpectedValues(array('project_id', 'used', 'comment', 'date'));
        list($valid, $values, $errors) = $this->reportingModel->validateUsage($values);

        if ($valid) {
            $this->assertProjectAccess($values['project_id']);

            if (empty($this->reportingModel->getBudgetByProject($this->userSession->getId(), $values['project_id']))) {
                throw new PageNotFoundException();
            }

            if ($this->reportingModel->createUsage($this->userSession->getId(), $values)) {
                $this->flash->success(t('Usage entry created successfully.'));
                return $this->redirectTo('ReportingUsed');
            }

            $errors['used'][] = t('Unable to create this usage entry.');
        }

        $this->ShowAddUsed($values, $errors);
    }

    /**
     * Display a destructive-action confirmation before deleting usage.
     */
    public function ConfirmDeleteUsed()
    {
        $usage = $this->getOwnedUsage();
        $this->assertProjectAccess($usage['project_id']);

        $this->response->html($this->template->render('Reporting:reporting/usage_remove', array(
            'usage' => $usage,
        )));
    }

    /**
     * Delete one usage entry within the current user's scope.
     */
    public function DeleteUsed()
    {
        $this->assertPostWithValidToken();
        $values = $this->getExpectedValues(array('id'));
        $usage = $this->getOwnedUsage(isset($values['id']) ? (int) $values['id'] : 0);
        $this->assertProjectAccess($usage['project_id']);

        if ($this->reportingModel->removeUsage($usage['id'], $this->userSession->getId())) {
            $this->flash->success(t('Usage entry removed successfully.'));
        } else {
            $this->flash->failure(t('Unable to remove this usage entry.'));
        }

        $this->redirectTo('ReportingUsed');
    }

    /**
     * Render one dashboard view from a consistent, user-scoped data set.
     *
     * @param string $template
     * @param string $title
     */
    private function renderDashboard($template, $title)
    {
        $user = $this->getCurrentUser();
        $projects = $this->getAccessibleProjects();
        $projectIds = array_column($projects, 'project_id');
        $budgets = $this->reportingModel->getBudgets($user['id'], $projectIds);
        $usage = $this->reportingModel->getUsage($user['id'], $projectIds);

        $this->response->html($this->helper->layout->dashboard($template, array(
            'budgets' => $budgets,
            'projects' => $projects,
            'summary' => $this->reportingModel->buildSummary($budgets, $usage),
            'title' => $title,
            'usage' => $usage,
            'usageByProject' => $this->reportingModel->groupUsageByProject($usage),
            'user' => $user,
        )));
    }

    /**
     * Return the authenticated user rather than accepting a user-id override.
     *
     * @return array
     */
    private function getCurrentUser()
    {
        $user = $this->userModel->getById($this->userSession->getId());

        if (empty($user)) {
            throw new AccessForbiddenException();
        }

        return $user;
    }

    /**
     * Return projects the current user is allowed to access.
     *
     * @return array
     */
    private function getAccessibleProjects()
    {
        if ($this->userSession->isAdmin()) {
            return $this->reportingModel->getAllActiveProjects();
        }

        return $this->reportingModel->getProjects(
            $this->projectPermissionModel->getActiveProjectIds($this->userSession->getId())
        );
    }

    /**
     * Return accessible projects that do not already have a user-owned budget.
     *
     * @return array
     */
    private function getAvailableBudgetProjects($includeProjectId = 0)
    {
        $projects = $this->getAccessibleProjects();
        $budgets = $this->reportingModel->getBudgets(
            $this->userSession->getId(),
            array_column($projects, 'project_id')
        );
        $budgeted = array_fill_keys(array_map('intval', array_column($budgets, 'project_id')), true);

        return array_values(array_filter($projects, function (array $project) use ($budgeted, $includeProjectId) {
            $projectId = (int) $project['project_id'];

            return $projectId === (int) $includeProjectId || !isset($budgeted[$projectId]);
        }));
    }

    /**
     * Build escaped form-helper options from project rows.
     *
     * @param  array $projects
     * @return array
     */
    private function getProjectOptions(array $projects)
    {
        $options = array();

        foreach ($projects as $project) {
            $options[(int) $project['project_id']] = $project['project_name'];
        }

        return $options;
    }

    /**
     * Require POST and Kanboard's one-time form token for every write.
     */
    private function assertPostWithValidToken()
    {
        if (!$this->request->isPost()) {
            throw new AccessForbiddenException();
        }

        $this->checkCSRFForm();
    }

    /**
     * Reject fields outside the exact action allowlist.
     *
     * @param  array $allowedFields
     * @return array
     */
    private function getExpectedValues(array $allowedFields)
    {
        $values = $this->request->getValues();

        if (array_diff(array_keys($values), $allowedFields) !== array()) {
            throw new AccessForbiddenException();
        }

        return array_intersect_key($values, array_flip($allowedFields));
    }

    /**
     * Verify that a project exists, is active and is visible to the current user.
     *
     * @param  integer $projectId
     * @return array
     */
    private function assertProjectAccess($projectId)
    {
        $project = $this->projectModel->getById((int) $projectId);

        if (
            empty($project) ||
            (int) $project['is_active'] !== 1 ||
            !$this->projectPermissionModel->isUserAllowed((int) $projectId, $this->userSession->getId())
        ) {
            throw new AccessForbiddenException();
        }

        return $project;
    }

    /**
     * Return one current-user budget without disclosing another user's record.
     *
     * @param  integer $id
     * @return array
     */
    private function getOwnedBudget($id = 0)
    {
        $id = $id > 0 ? $id : $this->request->getIntegerParam('id');
        $budget = $this->reportingModel->getBudgetById($id, $this->userSession->getId());

        if (empty($budget)) {
            throw new PageNotFoundException();
        }

        return $budget;
    }

    /**
     * Return one current-user usage row without disclosing another user's record.
     *
     * @param  integer $id
     * @return array
     */
    private function getOwnedUsage($id = 0)
    {
        $id = $id > 0 ? $id : $this->request->getIntegerParam('id');
        $usage = $this->reportingModel->getUsageById($id, $this->userSession->getId());

        if (empty($usage)) {
            throw new PageNotFoundException();
        }

        return $usage;
    }

    /**
     * Redirect to one Reporting dashboard page using Kanboard's base path.
     *
     * @param string $action
     */
    private function redirectTo($action)
    {
        $this->response->redirect($this->helper->url->to(
            'ReportingController',
            $action,
            array('plugin' => self::PLUGIN_NAME)
        ));
    }
}
