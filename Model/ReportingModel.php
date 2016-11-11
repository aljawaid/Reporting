<?php

namespace Kanboard\Plugin\Reporting\Model;

use PDO;
use Kanboard\Core\Base;
use Kanboard\Controller\BaseController;


class ReportingModel extends Base
{

  const TABLEprojects = 'projects';
  const TABLEbudget = 'reportingBudget';
  const TABLEused = 'reportingUsed';
  const TABLEaccess = 'project_has_users';

  public function repmGetProjects($user_id)
  {
    return $this->db
      ->table(self::TABLEaccess)
      ->columns(
        self::TABLEaccess.'.project_id',
        'tblPro.name AS project_name'
      )
      ->eq('user_id', $user_id)
      ->eq('tblPro.is_active', "1")
      ->left(self::TABLEprojects, 'tblPro', 'id', self::TABLEaccess, 'project_id')
      ->asc('project_id')
      ->findAll();
  }

  public function repmGetBudget($user_id, $projectAccess)
  {
    foreach($projectAccess as $u) $uids[] = $u['project_id'];
    $projectAccess = implode(", ",$uids);
    substr_replace($projectAccess, "", -2);
    $projectAccess = explode(', ', $projectAccess);

    return $this->db
      ->table(self::TABLEbudget)
      ->columns(
        self::TABLEbudget.'.id',
        self::TABLEbudget.'.project_id',
        self::TABLEbudget.'.user_id',
        self::TABLEbudget.'.budget',
        self::TABLEbudget.'.comment',
        self::TABLEbudget.'.date',
        'tblPro.name AS project_name'
      )
      ->eq('user_id', $user_id)
      ->in(self::TABLEbudget.'.project_id', $projectAccess)
      ->left(self::TABLEprojects, 'tblPro', 'id', self::TABLEbudget, 'project_id')
      ->asc('project_name')
      ->asc('date')
      ->findAll();

  }

  public function repmGetUsed($user_id, $projectAccess)
  {
    foreach($projectAccess as $u) $uids[] = $u['project_id'];
    $projectAccess = implode(", ",$uids);
    substr_replace($projectAccess, "", -2);
    $projectAccess = explode(', ', $projectAccess);

    return $this->db
      ->table(self::TABLEused)
      ->columns(
        self::TABLEused.'.id',
        self::TABLEused.'.project_id',
        self::TABLEused.'.user_id',
        self::TABLEused.'.used',
        self::TABLEused.'.comment',
        self::TABLEused.'.date',
        'tblPro.name AS project_name'
      )
      ->eq('user_id', $user_id)
      ->left(self::TABLEprojects, 'tblPro', 'id', self::TABLEused, 'project_id')
      ->in(self::TABLEused.'.project_id', $projectAccess)
      ->asc('project_name')
      ->asc('date')
      ->findAll();
  }

  public function repmDeleteBudget($id)
  {

    return $this->db
      ->table(self::TABLEbudget)
      ->eq('id', $id)
      ->remove();

  }

  public function repmDeleteUsed($user_id, $id)
  {

    return $this->db
      ->table(self::TABLEused)
      ->eq('user_id', $user_id)
      ->eq('id', $id)
      ->remove();

  }

  public function repmAddBudget($project_id, $user_id, $budget, $comment, $date)
  {

    $test = $this->db
      ->table(self::TABLEbudget)
      ->eq('project_id', $project_id)
      ->findOneColumn('id');

    if (empty($test)) {

    $values = array(
      'project_id' => $project_id,
      'user_id' => $user_id,
      'budget' => $budget,
      'comment' => $comment,
      'date' => $date
    );

    return $this->db
      ->table(self::TABLEbudget)
      ->insert($values);
    }
    }

  public function repmAddUsed($project_id, $user_id, $used, $comment, $date)
  {

    $values = array(
      'project_id' => $project_id,
      'user_id' => $user_id,
      'used' => $used,
      'comment' => $comment,
      'date' => $date
    );

    return $this->db
      ->table(self::TABLEused)
      ->insert($values);
    }

  public function repmEditBudget($id, $project_id, $user_id, $budget, $comment, $date)
  {
    $values = array(
      'project_id' => $project_id,
      'user_id' => $user_id,
      'budget' => $budget,
      'comment' => $comment,
      'date' => $date
    );

    return $this->db
      ->table(self::TABLEbudget)
      ->eq('id', $id)
      ->update($values);
    }

  public function repmGetUsedReport($user_id, $projectAccess)
  {
    $data = array();
    foreach($projectAccess as $q) {
      $tempData = $this->db
        ->table(self::TABLEused)
        ->columns(
          self::TABLEused.'.project_id',
          self::TABLEused.'.comment',
          self::TABLEused.'.date'
        )
        ->eq(self::TABLEused.'.project_id', $q['project_id'])
        ->eq(self::TABLEused.'.user_id', $user_id)
        ->asc('project_id')
        ->desc('date')
        ->findOne();

    if (!empty($tempData['project_id'])) {
      $data[] = $tempData;
    }
    unset($tempData);
    }
    return $data;
  }





  }
