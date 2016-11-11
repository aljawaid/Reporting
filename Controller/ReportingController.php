<?php

namespace Kanboard\Plugin\Reporting\Controller;

use Kanboard\Controller\BaseController;

class ReportingController extends BaseController
{

 /* Reporting budget */
  public function ReportingBudget()
  {
   //$project = $this->getProject();
   $user = $this->getUser();

   $projectAccess = $this->reportingModel->repmGetProjects($user['id']);

   return $this->response->html($this->helper->layout->dashboard('Reporting:reporting/budget', array(
     'title' => t('Reporting'),
     'user' => $user,
     'projectAccess' => $projectAccess,
     'budget' => $this->reportingModel->repmGetBudget($user['id'], $projectAccess),
     'used' => $this->reportingModel->repmGetUsed($user['id'], $projectAccess),
   )));
  }


 /* Reporting used */
  public function ReportingUsed()
  {
   //$project = $this->getProject();
   $user = $this->getUser();

   $projectAccess = $this->reportingModel->repmGetProjects($user['id']);

   return $this->response->html($this->helper->layout->dashboard('Reporting:reporting/used', array(
     'title' => t('Reporting'),
     'user' => $user,
     'projectAccess' => $projectAccess,
     'budget' => $this->reportingModel->repmGetBudget($user['id'], $projectAccess),
     'used' => $this->reportingModel->repmGetUsed($user['id'], $projectAccess),
   )));
  }


 /* Reporting show */
  public function ReportingReport()
  {
   //$project = $this->getProject();
   $user = $this->getUser();

   $projectAccess = $this->reportingModel->repmGetProjects($user['id']);

   return $this->response->html($this->helper->layout->dashboard('Reporting:reporting/report', array(
     'title' => t('Reporting'),
     'user' => $user,
     'projectAccess' => $projectAccess,
     'reportingData' => $this->reportingModel->repmGetUsedReport($user['id'], $projectAccess),
     'budget' => $this->reportingModel->repmGetBudget($user['id'], $projectAccess),
     'used' => $this->reportingModel->repmGetUsed($user['id'], $projectAccess),
   ), 'reporting:reporting/sidebar'));
  }



  /* Calls for used */

  public function AddUsed()
  {

   $user = $this->getUser();

   $project_id = $this->request->getStringParam('project_id');
   $used = $this->request->getStringParam('used');
   $comment = $this->request->getStringParam('comment');
   $date = $this->request->getStringParam('date');

   $validation = $this->reportingModel->repmAddUsed($project_id, $user['id'], $used, $comment, $date);

  }

  public function DeleteUsed()
  {

   $user = $this->getUser();
   $id = $this->request->getStringParam('id');
   $validation = $this->reportingModel->repmDeleteUsed($user['id'], $id);

  }


  /* Calls for budget */

  public function EditBudget()
  {

   $user = $this->getUser();

   $id = $this->request->getStringParam('id');
   $project_id = $this->request->getStringParam('project_id');
   $budget = $this->request->getStringParam('budget');
   $comment = $this->request->getStringParam('comment');
   $date = $this->request->getStringParam('date');

   $validation = $this->reportingModel->repmEditBudget($id, $project_id, $user['id'], $budget, $comment, $date);

  }

  public function AddBudget()
  {

   $user = $this->getUser();

   $project_id = $this->request->getStringParam('project_id');
   $budget = $this->request->getStringParam('budget');
   $comment = $this->request->getStringParam('comment');
   $date = $this->request->getStringParam('date');

   $validation = $this->reportingModel->repmAddBudget($project_id, $user['id'], $budget, $comment, $date);

  }


}
