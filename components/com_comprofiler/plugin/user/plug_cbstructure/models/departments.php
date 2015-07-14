<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class Departments{
  public static function getDepartments($profileId){

    $db = JFactory::getDbo();
    $db->setQuery('SELECT c.id, c.title, c.profile_id, c.url
                   FROM #__comprofiler_plugin_department_clinic c
                   WHERE c.profile_id='.$db->quote($profileId).' ORDER BY c.title');
    $clinics = $db->loadAssocList();
    array_unshift($clinics, array('id'=>0, 'title'=>null));
    for($i = 0 ; $i < count($clinics); $i++) {
      $db->setQuery('SELECT d.id, d.title, d.url, d.profile_id, u.username, u.id as employee_id, profile.avatar, profile.cb_sincelevel,du.position
                   FROM #__comprofiler_plugin_department d
                   LEFT JOIN #__comprofiler_plugin_department_employees du ON du.department_id=d.id
                   LEFT JOIN #__users u ON u.id=du.user_id
                   LEFT JOIN #__comprofiler profile ON profile.user_id=u.id
                   WHERE d.profile_id=' . $db->quote($profileId) . '
                    AND d.clinic_id='.$db->quote($clinics[$i]['id']).'
                   ORDER BY d.title, u.username');

      $departments = $db->loadObjectList();
      $result = array();
      $currentDepartment = null;
      foreach ($departments as $department) {

        if ($currentDepartment == null || $currentDepartment["id"] != $department->id) {
          if ($currentDepartment != null) {
            $result[] = $currentDepartment;
          }
          $currentDepartment = array('id' => $department->id,
            'title' => $department->title,
            'url' => $department->url,
            'profile_id' => $department->profile_id,
            'employees' => array());
        }

        if ($department->employee_id) {
          $currentDepartment['employees'][] = array(
            'id' => $department->employee_id,
            'name' => $department->username,
            "href" => JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $department->employee_id),
            "avatar" => $department->avatar,
            "cb_sincelevel" => $department->cb_sincelevel,
            "position" => $department->position
          );
        }

      }

      if ($currentDepartment != null) {
        $result[] = $currentDepartment;
      }
      $clinics[$i]['departments'] = $result;

    }
    return $clinics;
  }

  public static function addClinic($title, $profile_id, $url){
    $db = JFactory::getDbo();
    $db->setQuery('INSERT INTO #__comprofiler_plugin_department_clinic
                  (title, profile_id, url)
                  VALUES ('.$db->quote($title).','.$db->quote($profile_id).','.$db->quote($url).')');
    $db->execute();
  }

  public static function getClinic($clinic_id){
    $db = JFactory::getDbo();
    $db->setQuery('SELECT id, title, profile_id
                   FROM #__comprofiler_plugin_department_clinic
                   WHERE id='.$db->quote($clinic_id));
    return $db->loadObject();
  }

  public static function removeClinic($clinic_id){
    if(empty($clinic_id) || (!is_numeric($clinic_id)) || $clinic_id < 1){
      return;
    }
    $db = JFactory::getDbo();
    $db->setQuery('DELETE FROM #__comprofiler_plugin_department_employees
                   WHERE department_id IN (SELECT id from #__comprofiler_plugin_department where clinic_id='.$db->quote($clinic_id).')');
    $db->execute();

    $db->setQuery('DELETE FROM #__comprofiler_plugin_department WHERE clinic_id='.$db->quote($clinic_id));
    $db->execute();

    $db->setQuery('DELETE FROM #__comprofiler_plugin_department_clinic WHERE id='.$db->quote($clinic_id));
    $db->execute();
  }

  public static function addDepartment($title, $profile_id, $clinic_id, $url){
    $db = JFactory::getDbo();
    $db->setQuery('INSERT INTO #__comprofiler_plugin_department
                  (title, profile_id, clinic_id, url)
                  VALUES ('.$db->quote($title).','
                          .$db->quote($profile_id).','
                          .$db->quote($clinic_id).','
                          .$db->quote($url).')');
    $db->execute();
  }

  public static function updateDepartment($department_id, $title, $url){
    $db = JFactory::getDbo();
    $db->setQuery('UPDATE #__comprofiler_plugin_department
                  SET title='.$db->quote($title).',
                  url='.$db->quote($url).'
                  WHERE id='.$db->quote($department_id));
    $db->execute();
  }

  public static function getDepartment($departmentId){
    $db = JFactory::getDbo();
    $db->setQuery('SELECT id, title, published, profile_id
                   FROM #__comprofiler_plugin_department
                   WHERE id='.$db->quote($departmentId));

    return $db->loadObject();

  }
  public static function removeDepartment($departmentId){
    $db = JFactory::getDbo();
    $db->setQuery('DELETE FROM #__comprofiler_plugin_department_employees WHERE department_id='.$db->quote($departmentId));
    $db->execute();

    $db->setQuery('DELETE FROM #__comprofiler_plugin_department WHERE id='.$db->quote($departmentId));
    $db->execute();
  }
  public static function removeUserFromDeparmtent($employeeId, $departmentId){
    $db = JFactory::getDbo();
    $db->setQuery('DELETE FROM #__comprofiler_plugin_department_employees WHERE user_id='.$db->quote($employeeId).' AND department_id='.$db->quote($departmentId));
    $db->execute();
  }

  public static function addUserToDepartment($employeeId, $departmentId, $position){
    $db = JFactory::getDbo();
    $db->setQuery('INSERT INTO #__comprofiler_plugin_department_employees (user_id, department_id, position) VALUES ('.$db->quote($employeeId).' , '.$db->quote($departmentId).', '.$db->quote($position).')');
    $db->execute();
  }

  public static function isUserLinkedToDepartment($employeeId, $departmentId){
    $db = JFactory::getDbo();
    $db->setQuery('SELECT 1 FROM #__comprofiler_plugin_department_employees WHERE user_id='.$db->quote($employeeId).' AND department_id='.$db->quote($departmentId));
    return $db->loadResult() == "1";
  }
}
?>