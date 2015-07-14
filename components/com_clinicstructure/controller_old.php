<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once(dirname(__FILE__).'/../com_comprofiler/plugin/user/plug_cbstructure/models/departments.php');
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 * @since       1.5
 */
class ClinicstructureController extends JControllerLegacy
{
	/**
	 * Show the form so that the user can send the link to someone.
	 *
	 * @return  void
	 *
	 * @since 1.5
	 */
	public function add()
	{
    $app = JFactory::getApplication();
    $db = JFactory::getDbo();
    $user = JFactory::getUser();
    $isRoot = $user->authorise('core.admin');

    // JInput object
    $input = $app->input;
    header('Content-Type: application/json');

    //Only Authorised users can add departments.
    if ($user->get('guest') == 1)
    {
      echo json_encode(array("success"=>false, 'message'=>'Вы должны авторизироваться.'));
      JFactory::getApplication()->close();
      return;
    }

    $departmentName = $_POST['departmentName'];
    $profileId = $input->getInt('profileId');

    if(!( $user->get('id')==$profileId || $isRoot)){
      echo json_encode(array("success"=>false, 'message'=>'У вас нет прав добавлять отделения данному профайлу.'));
      JFactory::getApplication()->close();
      return;
    }

    if($departmentName){
      $db->setQuery("SELECT 1 FROM #__comprofiler_plugin_department WHERE title=".$db->quote($departmentName)." AND profile_id=".$db->quote($profileId));
      if($db->loadResult()=="1"){
        echo json_encode(array("success"=>false, 'message'=>'Отделение с именем '.$departmentName.' уже существует.'));
        JFactory::getApplication()->close();
        return;
      }
    }

    $db->setQuery('INSERT INTO #__comprofiler_plugin_department (title, profile_id) VALUES ('.$db->quote($departmentName).','.$db->quote($profileId).')');

    $db->execute();

    $result = array('success'=>true, 'departments'=>Departments::getDepartments($profileId));
    echo json_encode( $result );
    JFactory::getApplication()->close();
	}

  public function delete(){
    $app = JFactory::getApplication();
    $user = JFactory::getUser();
    $isRoot = $user->authorise('core.admin');

    $input = $app->input;
    header('Content-Type: application/json');

    //Only Authorised users can remove departments.
    if ($user->get('guest') == 1)
    {
      echo json_encode(array("success"=>false, 'message'=>'Вы должны авторизироваться.'));
      JFactory::getApplication()->close();
      return;
    }
    //OK now get request parameters
    $departmentId = $input->getInt('departmentId');

    //Check if department exists
    $department = Departments::getDepartment($departmentId);
    if($department==null){
      echo json_encode(array("success"=>false, 'message'=>'Такого отделения не существует.'));
      JFactory::getApplication()->close();
      return;
    }
    //Now check if this user has access rights to perform this operation
    if(!($isRoot || $user->get('id')==$department->profile_id )){
      echo json_encode(array("success"=>false, 'message'=>'У вас недостаточно прав для выполнения операции.'));
      JFactory::getApplication()->close();
      return;
    }

    //OK now we can add user to department

    Departments::removeDepartment($departmentId);

    $result = array('success'=>true, 'departments'=>Departments::getDepartments($department->profile_id));
    echo json_encode( $result );
    JFactory::getApplication()->close();
  }

  public function addEmployeeToDepartment(){
    $app = JFactory::getApplication();
    $user = JFactory::getUser();
    $isRoot = $user->authorise('core.admin');

    $input = $app->input;
    header('Content-Type: application/json');

    //Only Authorised users can add departments.
    if ($user->get('guest') == 1)
    {
      echo json_encode(array("success"=>false, 'message'=>'Вы должны авторизироваться.'));
      JFactory::getApplication()->close();
      return;
    }


    //OK now get request parameters
    $employeeId = $input->getInt('employeeId');
    $departmentId = $input->getInt('departmentId');
    //Check if department exists
    $department = Departments::getDepartment($departmentId);
    if($department==null){
      echo json_encode(array("success"=>false, 'message'=>'Такого отделения не существует.'));
      JFactory::getApplication()->close();
      return;
    }
    //Now check if this user has access rights to perform this operation
    if(!($isRoot || $user->get('id')==$department->profile_id || $user->get('id') == $employeeId)){
      echo json_encode(array("success"=>false, 'message'=>'У вас недостаточно прав для выполнения операции.'));
      JFactory::getApplication()->close();
      return;
    }

    //Check if user is already linked to department
    if(Departments::isUserLinkedToDepartment($employeeId, $departmentId)){
      echo json_encode(array("success"=>false, 'message'=>'Пользователь уже добавлен.'));
      JFactory::getApplication()->close();
      return;
    }
    //OK now we can add user to department

    Departments::addUserToDepartment($employeeId, $departmentId);

    $result = array('success'=>true, 'departments'=>Departments::getDepartments($department->profile_id));
    echo json_encode( $result );
    JFactory::getApplication()->close();
  }

  public function removeEmployeeFromDepartment(){
    $app = JFactory::getApplication();
    $user = JFactory::getUser();
    $isRoot = $user->authorise('core.admin');

    $input = $app->input;
    header('Content-Type: application/json');

    //Only Authorised users can add departments.
    if ($user->get('guest') == 1)
    {
      echo json_encode(array("success"=>false, 'message'=>'Вы должны авторизироваться.'));
      JFactory::getApplication()->close();
      return;
    }
    //OK now get request parameters
    $employeeId = $input->getInt('employeeId');
    $departmentId = $input->getInt('departmentId');
    //Check if department exists
    $department = Departments::getDepartment($departmentId);
    if($department==null){
      echo json_encode(array("success"=>false, 'message'=>'Такого отделения не существует.'));
      JFactory::getApplication()->close();
      return;
    }
    //Now check if this user has access rights to perform this operation
    if(!($isRoot || $user->get('id')==$department->profile_id || $user->get('id') == $employeeId)){
      echo json_encode(array("success"=>false, 'message'=>'У вас недостаточно прав для выполнения операции.'));
      JFactory::getApplication()->close();
      return;
    }

    //OK now we can remove user from department

    Departments::removeUserFromDeparmtent($employeeId, $departmentId);

    $result = array('success'=>true, 'departments'=>Departments::getDepartments($department->profile_id));
    echo json_encode( $result );
    JFactory::getApplication()->close();
  }
}
