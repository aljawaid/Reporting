<?php

/**
 * Provides user-scoped budget and usage storage for Reporting.
 *
 * Related test files: tests/ReportingModelTest.php and
 * tests/DatabaseMigrationTest.php.
 */

namespace Kanboard\Plugin\Reporting\Model;

use DateTime;
use Kanboard\Core\Base;

class ReportingModel extends Base
{
    const TABLE_PROJECTS = 'projects';
    const TABLE_BUDGETS = 'reporting_budgets';
    const TABLE_USAGE = 'reporting_usage';

    /**
     * Return active projects from an already authorised project-id list.
     *
     * @param  array $projectIds
     * @return array
     */
    public function getProjects(array $projectIds)
    {
        $projectIds = $this->normalizeProjectIds($projectIds);

        if ($projectIds === array()) {
            return array();
        }

        return $this->db
            ->table(self::TABLE_PROJECTS)
            ->columns('id AS project_id', 'name AS project_name')
            ->eq('is_active', 1)
            ->in('id', $projectIds)
            ->asc('name')
            ->findAll();
    }

    /**
     * Return every active project for an administrator.
     *
     * @return array
     */
    public function getAllActiveProjects()
    {
        return $this->db
            ->table(self::TABLE_PROJECTS)
            ->columns('id AS project_id', 'name AS project_name')
            ->eq('is_active', 1)
            ->asc('name')
            ->findAll();
    }

    /**
     * Return budgets owned by one user and limited to accessible projects.
     *
     * @param  integer $userId
     * @param  array   $projectIds
     * @return array
     */
    public function getBudgets($userId, array $projectIds)
    {
        $projectIds = $this->normalizeProjectIds($projectIds);

        if ($projectIds === array()) {
            return array();
        }

        return $this->db
            ->table(self::TABLE_BUDGETS)
            ->columns(
                self::TABLE_BUDGETS . '.id',
                self::TABLE_BUDGETS . '.project_id',
                self::TABLE_BUDGETS . '.user_id',
                self::TABLE_BUDGETS . '.budget',
                self::TABLE_BUDGETS . '.comment',
                self::TABLE_BUDGETS . '.date',
                self::TABLE_PROJECTS . '.name AS project_name'
            )
            ->eq(self::TABLE_BUDGETS . '.user_id', (int) $userId)
            ->in(self::TABLE_BUDGETS . '.project_id', $projectIds)
            ->join(self::TABLE_PROJECTS, 'id', 'project_id')
            ->asc(self::TABLE_PROJECTS . '.name')
            ->asc(self::TABLE_BUDGETS . '.date')
            ->findAll();
    }

    /**
     * Return usage entries owned by one user and limited to accessible projects.
     *
     * @param  integer $userId
     * @param  array   $projectIds
     * @return array
     */
    public function getUsage($userId, array $projectIds)
    {
        $projectIds = $this->normalizeProjectIds($projectIds);

        if ($projectIds === array()) {
            return array();
        }

        return $this->db
            ->table(self::TABLE_USAGE)
            ->columns(
                self::TABLE_USAGE . '.id',
                self::TABLE_USAGE . '.project_id',
                self::TABLE_USAGE . '.user_id',
                self::TABLE_USAGE . '.used',
                self::TABLE_USAGE . '.comment',
                self::TABLE_USAGE . '.date',
                self::TABLE_PROJECTS . '.name AS project_name'
            )
            ->eq(self::TABLE_USAGE . '.user_id', (int) $userId)
            ->in(self::TABLE_USAGE . '.project_id', $projectIds)
            ->join(self::TABLE_PROJECTS, 'id', 'project_id')
            ->asc(self::TABLE_PROJECTS . '.name')
            ->desc(self::TABLE_USAGE . '.date')
            ->desc(self::TABLE_USAGE . '.id')
            ->findAll();
    }

    /**
     * Return one user-owned budget record.
     *
     * @param  integer $id
     * @param  integer $userId
     * @return array
     */
    public function getBudgetById($id, $userId)
    {
        $budget = $this->db
            ->table(self::TABLE_BUDGETS)
            ->eq('id', (int) $id)
            ->eq('user_id', (int) $userId)
            ->findOne();

        return $budget ?: array();
    }

    /**
     * Return one user-owned usage record.
     *
     * @param  integer $id
     * @param  integer $userId
     * @return array
     */
    public function getUsageById($id, $userId)
    {
        $usage = $this->db
            ->table(self::TABLE_USAGE)
            ->eq('id', (int) $id)
            ->eq('user_id', (int) $userId)
            ->findOne();

        return $usage ?: array();
    }

    /**
     * Return a user's budget for one project.
     *
     * @param  integer $userId
     * @param  integer $projectId
     * @return array
     */
    public function getBudgetByProject($userId, $projectId)
    {
        $budget = $this->db
            ->table(self::TABLE_BUDGETS)
            ->eq('user_id', (int) $userId)
            ->eq('project_id', (int) $projectId)
            ->findOne();

        return $budget ?: array();
    }

    /**
     * Validate and normalise one budget form payload.
     *
     * @param  array $values
     * @return array
     */
    public function validateBudget(array $values)
    {
        return $this->validateRecord($values, 'budget');
    }

    /**
     * Validate and normalise one usage form payload.
     *
     * @param  array $values
     * @return array
     */
    public function validateUsage(array $values)
    {
        return $this->validateRecord($values, 'used');
    }

    /**
     * Create one budget when the user does not already have one for the project.
     *
     * @param  integer $userId
     * @param  array   $values
     * @return boolean
     */
    public function createBudget($userId, array $values)
    {
        if (!empty($this->getBudgetByProject($userId, $values['project_id']))) {
            return false;
        }

        return $this->db->table(self::TABLE_BUDGETS)->insert(array(
            'project_id' => (int) $values['project_id'],
            'user_id' => (int) $userId,
            'budget' => $values['budget'],
            'comment' => $values['comment'],
            'date' => $values['date'],
        ));
    }

    /**
     * Update one budget without permitting project or owner reassignment.
     *
     * @param  integer $id
     * @param  integer $userId
     * @param  array   $values
     * @return boolean
     */
    public function updateBudget($id, $userId, array $values)
    {
        if (empty($this->getBudgetById($id, $userId))) {
            return false;
        }

        return $this->db
            ->table(self::TABLE_BUDGETS)
            ->eq('id', (int) $id)
            ->eq('user_id', (int) $userId)
            ->update(array(
                'budget' => $values['budget'],
                'comment' => $values['comment'],
                'date' => $values['date'],
            ));
    }

    /**
     * Remove one user-owned budget and its associated usage entries.
     *
     * @param  integer $id
     * @param  integer $userId
     * @return boolean
     */
    public function removeBudget($id, $userId)
    {
        $budget = $this->getBudgetById($id, $userId);

        if (empty($budget)) {
            return false;
        }

        $this->db->startTransaction();
        $usageRemoved = $this->db
            ->table(self::TABLE_USAGE)
            ->eq('project_id', (int) $budget['project_id'])
            ->eq('user_id', (int) $userId)
            ->remove();
        $budgetRemoved = $this->db
            ->table(self::TABLE_BUDGETS)
            ->eq('id', (int) $id)
            ->eq('user_id', (int) $userId)
            ->remove();

        if ($usageRemoved !== false && $budgetRemoved) {
            $this->db->closeTransaction();
            return true;
        }

        $this->db->cancelTransaction();
        return false;
    }

    /**
     * Create one user-owned usage entry.
     *
     * @param  integer $userId
     * @param  array   $values
     * @return boolean
     */
    public function createUsage($userId, array $values)
    {
        return $this->db->table(self::TABLE_USAGE)->insert(array(
            'project_id' => (int) $values['project_id'],
            'user_id' => (int) $userId,
            'used' => $values['used'],
            'comment' => $values['comment'],
            'date' => $values['date'],
        ));
    }

    /**
     * Remove one user-owned usage entry.
     *
     * @param  integer $id
     * @param  integer $userId
     * @return boolean
     */
    public function removeUsage($id, $userId)
    {
        if (empty($this->getUsageById($id, $userId))) {
            return false;
        }

        return $this->db
            ->table(self::TABLE_USAGE)
            ->eq('id', (int) $id)
            ->eq('user_id', (int) $userId)
            ->remove();
    }

    /**
     * Build display-safe totals without dividing by zero on legacy rows.
     *
     * @param  array $budgets
     * @param  array $usage
     * @return array
     */
    public function buildSummary(array $budgets, array $usage)
    {
        $usageByProject = array();
        $latestByProject = array();

        foreach ($usage as $entry) {
            $projectId = (int) $entry['project_id'];

            if (!isset($usageByProject[$projectId])) {
                $usageByProject[$projectId] = 0.0;
                $latestByProject[$projectId] = $entry;
            }

            $usageByProject[$projectId] += (float) $entry['used'];
        }

        $summary = array();

        foreach ($budgets as $budget) {
            $projectId = (int) $budget['project_id'];
            $budgetAmount = (float) $budget['budget'];
            $usedAmount = isset($usageByProject[$projectId]) ? $usageByProject[$projectId] : 0.0;
            $remainingAmount = $budgetAmount - $usedAmount;
            $usedPercentage = $budgetAmount > 0 ? ($usedAmount / $budgetAmount) * 100 : 0.0;

            $summary[] = $budget + array(
                'used_total' => $usedAmount,
                'remaining_total' => $remainingAmount,
                'used_percentage' => $usedPercentage,
                'remaining_percentage' => $budgetAmount > 0 ? ($remainingAmount / $budgetAmount) * 100 : 0.0,
                'latest_usage' => isset($latestByProject[$projectId]) ? $latestByProject[$projectId] : array(),
            );
        }

        return $summary;
    }

    /**
     * Group usage entries by project for accessible rendering.
     *
     * @param  array $usage
     * @return array
     */
    public function groupUsageByProject(array $usage)
    {
        $grouped = array();

        foreach ($usage as $entry) {
            $projectId = (int) $entry['project_id'];

            if (!isset($grouped[$projectId])) {
                $grouped[$projectId] = array();
            }

            $grouped[$projectId][] = $entry;
        }

        return $grouped;
    }

    /**
     * Keep legacy template/controller calls functional during staged upgrades.
     *
     * @param  integer $userId
     * @return array
     */
    public function repmGetProjects($userId)
    {
        if ($this->userSession->isAdmin()) {
            return $this->getAllActiveProjects();
        }

        return $this->getProjects($this->projectPermissionModel->getActiveProjectIds($userId));
    }

    public function repmGetBudget($userId, array $projectAccess)
    {
        return $this->getBudgets($userId, array_column($projectAccess, 'project_id'));
    }

    public function repmGetUsed($userId, array $projectAccess)
    {
        return $this->getUsage($userId, array_column($projectAccess, 'project_id'));
    }

    public function repmDeleteBudget($id)
    {
        return $this->removeBudget($id, $this->userSession->getId());
    }

    public function repmDeleteUsed($userId, $id)
    {
        return $this->removeUsage($id, $userId);
    }

    public function repmAddBudget($projectId, $userId, $budget, $comment, $date)
    {
        return $this->createBudget($userId, array(
            'project_id' => $projectId,
            'budget' => $budget,
            'comment' => $comment,
            'date' => $date,
        ));
    }

    public function repmAddUsed($projectId, $userId, $used, $comment, $date)
    {
        return $this->createUsage($userId, array(
            'project_id' => $projectId,
            'used' => $used,
            'comment' => $comment,
            'date' => $date,
        ));
    }

    public function repmEditBudget($id, $projectId, $userId, $budget, $comment, $date)
    {
        return $this->updateBudget($id, $userId, array(
            'budget' => $budget,
            'comment' => $comment,
            'date' => $date,
        ));
    }

    public function repmGetUsedReport($userId, array $projectAccess)
    {
        $usage = $this->getUsage($userId, array_column($projectAccess, 'project_id'));
        $latest = array();

        foreach ($usage as $entry) {
            $projectId = (int) $entry['project_id'];

            if (!isset($latest[$projectId])) {
                $latest[$projectId] = $entry;
            }
        }

        return array_values($latest);
    }

    /**
     * Validate common amount, date and comment fields.
     *
     * @param  array  $values
     * @param  string $amountField
     * @return array
     */
    private function validateRecord(array $values, $amountField)
    {
        $normalized = array(
            'project_id' => isset($values['project_id']) ? (int) $values['project_id'] : 0,
            $amountField => isset($values[$amountField]) ? trim((string) $values[$amountField]) : '',
            'comment' => isset($values['comment']) ? trim((string) $values['comment']) : '',
            'date' => isset($values['date']) ? trim((string) $values['date']) : '',
        );
        $errors = array();

        if ($normalized['project_id'] < 1) {
            $errors['project_id'][] = t('Select an accessible project.');
        }

        $amount = $this->normalizeDecimal($normalized[$amountField]);

        if ($amount === null || (float) $amount <= 0) {
            $errors[$amountField][] = t('Enter an amount greater than zero with no more than two decimal places.');
        } else {
            $normalized[$amountField] = $amount;
        }

        $date = DateTime::createFromFormat('!Y-m-d', $normalized['date']);
        $dateErrors = DateTime::getLastErrors();

        if (
            $date === false ||
            ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) ||
            $date->format('Y-m-d') !== $normalized['date']
        ) {
            $errors['date'][] = t('Enter a valid date.');
        }

        if (mb_strlen($normalized['comment']) > 4000) {
            $errors['comment'][] = t('Use no more than 4,000 characters.');
        }

        return array(empty($errors), $normalized, $errors);
    }

    /**
     * Normalise a positive DECIMAL(19,2) input without locale ambiguity.
     *
     * @param  string $value
     * @return string|null
     */
    private function normalizeDecimal($value)
    {
        if (!preg_match('/^(?:0|[1-9][0-9]{0,16})(?:\.[0-9]{1,2})?$/', $value)) {
            return null;
        }

        $parts = explode('.', $value, 2);
        $fraction = isset($parts[1]) ? str_pad($parts[1], 2, '0') : '00';

        return $parts[0] . '.' . $fraction;
    }

    /**
     * Keep only unique positive integer project ids.
     *
     * @param  array $projectIds
     * @return array
     */
    private function normalizeProjectIds(array $projectIds)
    {
        $normalized = array();

        foreach ($projectIds as $projectId) {
            $projectId = (int) $projectId;

            if ($projectId > 0) {
                $normalized[$projectId] = $projectId;
            }
        }

        return array_values($normalized);
    }
}
