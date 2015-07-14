<?php
/**
 * Community Builder (TM)
 * @version $Id: $
 * @package CommunityBuilder
 * @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) {
  die( 'Direct Access to this location is not allowed.' );
}


/**
 * Class cbconsultationsTab
 * Tab for CB consultations
 */

class cbstructureTab extends cbTabHandler
{
  /**
   * Returns a profile view tab title
   *
   * @param  TabTable   $tab       the tab database entry
   * @param  UserTable  $user      the user being displayed
   * @param  int        $ui        1 for front-end, 2 for back-end
   * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
   * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
   */
  
  public function getTabTitle( $tab, $user, $ui, $postdata )
  {

    return parent::getTabTitle( $tab, $user, $ui, $postdata );
  }

  /**
   * Generates the HTML to display the user profile tab
   *
   * @param  \CB\Database\Table\TabTable   $tab       the tab database entry
   * @param  \CB\Database\Table\UserTable  $user      the user being displayed
   * @param  int                           $ui        1 for front-end, 2 for back-end
   * @return string|boolean                           Either string HTML for tab content, or false if ErrorMSG generated
   */
  public function getDisplayTab( $tab, $user, $ui )
  {
    $document = JFactory::getDocument();
    $document->addScript('/media/jui/js/jquery.min.js');
    $document->addScript('/media/jui/js/bootstrap.min.js');

    require_once('models/departments.php');
    require_once('templates/departments.php');
    $my = JFactory::getUser();
    $myIsRoot = $my->authorise('core.admin');

    outputCbJs( 1 );
    outputCbTemplate( 1 );


    $result = '<div class="alert alert-dismissible" role="alert" id="departmentStatusContainer" style="display:none">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <span id="departmentStatus"></span>
</div>';

    $result .= '<div id="departmentsContainer"> ';

    $clinics = Departments::getDepartments($user->id);
    $result .= DepartmentsView::renderDepartments($clinics, $user->id, $my, $myIsRoot);

    $result .= '</div>';

    $result .='
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Введите свою должность</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal">
          <div class="form-group">
          <input type="hidden"  id="myModal_employeeId" />
          <input type="hidden" id="myModal_departmentId" />
          <input type="text" class="form-control" id="myModal_position" placeholder="Ваша должность"/>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-primary" onclick="addEmployeeToDepartmentSubmit()" >Добавить себя</button>
      </div>
    </div>
  </div>
</div>';

      $result .= '<script>

  function addNewClinic(){

    if(jQuery(\'#clinicName\').val() == ""){
      showMessage(false, "Введите название клиники");
      return;
    }
    jQuery.ajax({
      type: "POST",
      url: "'.JRoute::_("/index.php?option=com_clinicstructure&task=add_clinic").'",
      data: {clinicName:jQuery(\'#clinicName\').val(),
             clinicUrl:jQuery(\'#clinicUrl\').val(),
             profileId:"'.$user->id.'"},
      success: function(data){
        showMessage(data.success, data.message);
        updateDepartments(data.departments);
      }
    });
  }

  function addNewDepartment(clinicNumber){
    if(jQuery(\'#departmentName\'+clinicNumber).val() == ""){
      showMessage(false,"Введите название отделения");
      return;
    }
    jQuery.ajax({
      type: "POST",
      url: "'.JRoute::_("/index.php?option=com_clinicstructure&task=add").'",
      data: {clinicId:jQuery(\'#clinicId\'+clinicNumber).val(),
             departmentName:jQuery(\'#departmentName\'+clinicNumber).val(),
             departmentUrl:jQuery(\'#departmentUrl\'+clinicNumber).val(),
             profileId:"'.$user->id.'"},
      success: function(data){
        showMessage(data.success, data.message);
        updateDepartments(data.departments);
      }
    });
  }

  function deleteClinic(id){
    if(window.confirm("Вы действительно хотите удалить клинику?")){
    jQuery.ajax({
      type: "POST",
      url: "' . JRoute::_("/index.php?option=com_clinicstructure&task=delete_clinic") . '",
      data: {clinicId:id},
      success: function(data){
        showMessage(data.success, data.message);
        updateDepartments(data.departments);
      }
    });
    }
  }

  function deleteDepartment(id){
    if(window.confirm("Вы действительно хотите удалить отделение?")){
    jQuery.ajax({
      type: "POST",
      url: "' . JRoute::_("/index.php?option=com_clinicstructure&task=delete") . '",
      data: {departmentId:id},
      success: function(data){
        showMessage(data.success, data.message);
        updateDepartments(data.departments);
      }
    });
    }
  }

  function removeEmployeeFromDepartment(employeeId, departmentId){
    jQuery.ajax({
      type: "POST",
      url: "'.JRoute::_("/index.php?option=com_clinicstructure&task=removeEmployeeFromDepartment").'",
      data: {departmentId:departmentId, employeeId:employeeId},
      success: function(data){
        showMessage(data.success, data.message);
        updateDepartments(data.departments);
      }
    });
  }

  function addEmployeeToDepartment(employeeId, departmentId){
    jQuery(\'#myModal_employeeId\').val(employeeId);
    jQuery(\'#myModal_departmentId\').val(departmentId);
    jQuery(\'#myModal\').modal(\'show\');
  }

  function addEmployeeToDepartmentSubmit(){
    var employeeId = jQuery(\'#myModal_employeeId\').val();
    var departmentId = jQuery(\'#myModal_departmentId\').val();
    var position = jQuery(\'#myModal_position\').val();
    jQuery(\'#myModal\').modal(\'hide\');
    jQuery.ajax({
      type: "POST",
      url: "'.JRoute::_("/index.php?option=com_clinicstructure&task=addEmployeeToDepartment").'",
      data: {departmentId:departmentId, employeeId:employeeId, position:position},
      success: function(data){
        showMessage(data.success, data.message);
        updateDepartments(data.departments);
      }
    });
  }

  function showMessage(success, msg){
    jQuery("#departmentStatus").html(msg);
    if(success){
      jQuery("#departmentStatusContainer").addClass("alert-success");
      jQuery("#departmentStatusContainer").removeClass("alert-danger");
    }else{
      jQuery("#departmentStatusContainer").removeClass("alert-success");
      jQuery("#departmentStatusContainer").addClass("alert-danger");
    }
    jQuery("#departmentStatusContainer").show();
  }

  function updateDepartments(departments){
    jQuery("#departmentsContainer").html(departments);
  }
</script>';


    return $result;
  }
}
