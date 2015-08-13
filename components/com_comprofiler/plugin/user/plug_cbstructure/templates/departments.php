<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class DepartmentsView{
  public static function renderDepartments($clinics, $organizationId, $my, $myIsRoot){
    $result = "";
    $k=0;
    foreach($clinics as $clinic){
      if(!empty($clinic['title'])){
        $result .= '<h3>';
        if($clinic['url']){
          $result .= '<a href="'.$clinic['url'].'">'.$clinic['title'].'</a>';
        }else{
          $result .= $clinic['title'];
        }
        if($my->get('id') == $organizationId ||$myIsRoot) {
          $result .= '&nbsp;<small><a onclick="deleteClinic(\'' . $clinic['id'] . '\');return false;" class="text-danger" href="#">Удалить клинику</a></small>';
        }
        $result .= '</h3>';
      }
      $departments = $clinic['departments'];
      foreach($departments as $department){
        $result .= "<h4>";
        if($department['url']){
          $result.= '<a href="'.$department['url'].'">'.$department['title'].'</a>';
        }else{
          $result.=$department['title'];
        }
        if($my->get('id') == $organizationId ||$myIsRoot) {
          $result .= '&nbsp;<small><a onclick="deleteDepartment(\'' . $department['id'] . '\');return false;" class="text-danger" href="#">Удалить отделение</a></small>';
        }
        if($my->get('guest') != 1 && $my->get('id') != $organizationId &&
          (in_array(9, $my->groups) || in_array(15, $my->groups)||$myIsRoot )) {
          $result .= '&nbsp;<small><a  onclick="addEmployeeToDepartment(\'' . $my->get('id') . '\',\'' . $department['id'] . '\');return false;" class="text-success" href="#">Добавить себя</a></small>';
        }
        $result .= "</h4>";
        $result .='<div class="container-fluid">';
        foreach($department['employees'] as $employee){

          $result .='<div class="row"><div class="col-md-2">';
          if($employee['avatar']) {
            $result .= "<img  src=\"/images/comprofiler/{$employee['avatar']}\" class=\"cbImgPict cbThumbPict img-thumbnail\"/>";
          }

          $result .= '</div><div class="col-md-10">';
          $result .= "<h5><a href=\"" . JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $employee['id']) . "\">" . $employee['name'] . "</a>";
          if($my->get('id')==$employee['id'] || $my->get('id')==$organizationId || $myIsRoot){
            $result .= '&nbsp;<small><a  onclick="removeEmployeeFromDepartment(\''.$employee['id'].'\',\''.$department['id'].'\');return false;" class="text-success" href="#">Удалить связь</a></small>';
          }
          $result .= "</h5>";
          $result .= $employee['cb_sincelevel'];
          if($employee['position']){
            $result .= '<br/>'.$employee['position'];
          }
          $result .= '</div></div>';


        }
        $result .= '</div>';

      }
      if($my->get("id")==$organizationId || $myIsRoot) {
        $result .= '<br/>';
        $result .= '<div id="addDepartmentContainer'.$k.'" style="display:none" >';
        $result .= '<form class="form-horizontal">';
        $result .= '<div class="form-group">';
        $result .= '<label for="departmentName'.$k.'" class="col-sm-2 control-label">Отделение</label>';
        $result .= '<div class="col-sm-10">';
        $result .= '<input type="text" class="form-control" id="departmentName'.$k.'" placeholder="Новое отделение"/>';
        $result .= '<input type="hidden" id="clinicId'.$k.'" value="'.$clinic['id'].'"/>';
        $result .= '</div>';
        $result .= '</div>';
        $result .= '<div class="form-group">';
        $result .= '<label for="departmentUrl'.$k.'" class="col-sm-2 control-label">URL</label>';
        $result .= '<div class="col-sm-10">';
        $result .= '<input type="text" class="form-control" id="departmentUrl'.$k.'" placeholder="Ссылка на сайт"/>';
        $result .= '</div>';
        $result .= '</div>';
        $result .= '<div class="form-group">';
        $result .= '<div class="col-sm-offset-2 col-sm-10">';
        $result .= '<button type="submit" onclick="addNewDepartment('.$k.');return false;" class="btn btn-success">Добавить</button>';
        $result .= '<button type="submit" onclick="jQuery(\'#departmentName'.$k.'\').val(\'\');jQuery(\'#addDepartmentContainer'.$k.'\').hide();jQuery(\'#addDepartmentButton'.$k.'\').show();return false;" class="btn btn-danger">Отмена</button>';
        $result .= '</div>';
        $result .= '</div>';
        $result .= '</form>';
        $result .= '</div>';
        $result .= '<button type="button" onclick="jQuery(\'#addDepartmentContainer'.$k.'\').show();jQuery(\'#addDepartmentButton'.$k.'\').hide();return false" id="addDepartmentButton'.$k.'" class="btn btn-success">Добавить отделение</button>';
      }
      $k++;
    }
    if($my->get("id")==$organizationId || $myIsRoot) {
      $result .= '<br/><br/><br/>';
      $result .= '<div id="addClinicContainer" style="display:none" >';
      $result .= '<form class="form-horizontal">';
      $result .= '<div class="form-group">';
      $result .= '<label for="clinicName" class="col-sm-2 control-label">Клиника</label>';
      $result .= '<div class="col-sm-10">';
      $result .= '<input type="text" class="form-control" id="clinicName" placeholder="Новая клиника"/>';
      $result .= '</div>';
      $result .= '</div>';
      $result .= '<div class="form-group">';
      $result .= '<label for="clinicUrl" class="col-sm-2 control-label">URL</label>';
      $result .= '<div class="col-sm-10">';
      $result .= '<input type="text" class="form-control" id="clinicUrl" placeholder="Ссылка на сайт"/>';
      $result .= '</div>';
      $result .= '</div>';
      $result .= '<div class="form-group">';
      $result .= '<div class="col-sm-offset-2 col-sm-10">';
      $result .= '<button type="submit" onclick="addNewClinic();return false;" class="btn btn-success">Добавить</button>';
      $result .= '<button type="submit" onclick="jQuery(\'#clinicName\').val(\'\');jQuery(\'#addClinicContainer\').hide();jQuery(\'#addClinicButton\').show();return false;" class="btn btn-danger">Отмена</button>';
      $result .= '</div>';
      $result .= '</div>';
      $result .= '</form>';
      $result .= '</div>';
      $result .= '<button type="button" onclick="jQuery(\'#addClinicContainer\').show();jQuery(\'#addClinicButton\').hide();return false" id="addClinicButton" class="btn btn-success">Добавить клинику</button>';
    }
    return $result;
  }
}