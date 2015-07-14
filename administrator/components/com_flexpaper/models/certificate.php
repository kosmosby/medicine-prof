<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * HelloWorld Model
 */
class flexpaperModelCertificate extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	JTable	A database object
     * @since	2.5
     */
    public function getTable($type = 'flexpaper', $prefix = 'flexpaperTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
    /**
     * Method to get the record form.
     *
     * @param	array	$data		Data for the form.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     * @return	mixed	A JForm object on success, false on failure
     * @since	2.5
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_flexpaper.flexpaper', 'flexpaper',
            array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form))
        {
            return false;
        }
        return $form;
    }
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	2.5
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_flexpaper.edit.flexpaper.data', array());
        if (empty($data))
        {
            $data = $this->getItem();
        }
        return $data;
    }

    protected function update_certificates_table_with_date_column() {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT * FROM #__lms_results ORDER BY date_created";

        $db->setQuery($query);
        $rows = $db->loadObjectList();

//        echo "<pre>";
//        print_r($rows); die;


        for($i=0;$i<count($rows);$i++) {
            $query = "UPDATE #__flexpaper_certificate SET `date_created` = '".$rows[$i]->date_created."' WHERE tid = ".$rows[$i]->tid." AND user_id = ".$rows[$i]->userid;
            $db->setQuery($query);
            $db->query();
            echo $db->geterrormsg();
        }

    }

    protected function migrate_users() {
      /* some code for users migrate */

            JLoader::import( 'registration', JPATH_SITE . DS . 'components' . DS . 'com_users' . DS . 'models' );
            $reg_user_model = JModel::getInstance( 'registration', 'UsersModel' );

            //$rows = $this->get_users_not_in_db();
            $rows = $this->get_old_users();

            for($i=0;$i<count($rows);$i++) {
                $data['name'] = $rows[$i]->adi.' '.$rows[$i]->soyadi;
                $data['username'] = $rows[$i]->adi.' '.$rows[$i]->soyadi;
                $data['password1'] = $rows[$i]->sifre;
                $data['password2'] = $rows[$i]->sifre;
                $data['email1'] = $rows[$i]->email;
                $data['email2'] = $rows[$i]->email;
                $data['old_id'] = $rows[$i]->id;
                $data['registerDate'] = $rows[$i]->tarih;

                    $return = $reg_user_model->register($data);

                //echo $return.'<hr />';
            }
        die;
    /*****************/
    }

    protected function get_old_users() {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        // Select some fields
        $query = 'SELECT * FROM kullanicilar';

        $db->setQuery($query);
        $rows = $db->loadObjectList();
        return $rows;
    }

    protected function get_users_not_in_db() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        // Select some fields
        $query = 'select * from kullanicilar where id not in (SELECT a.id FROM kullanicilar as a, #__users as b where b.old_id = a.id)';

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    protected function compare_courses() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        // Select some fields
        $query = 'SELECT adi,id,uzun_icerik,kisa_icerik,resim_buyuk,fiyati FROM egitimler';

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        // echo "<pre>";
        // print_r($rows); die;

        $row = array();

        for($i=0;$i<count($rows);$i++) {
            $query = "select id from #__osemsc_acl WHERE title like '%".$rows[$i]->adi."%'";

            $db->setQuery($query);
            $row = $db->loadObject();
    
            if($row->id) {

                $image = "/components/com_osemsc/assets/msc_logo/".$rows[$i]->resim_buyuk;

                $query = "UPDATE #__osemsc_acl set `old_id` = ".$rows[$i]->id." /*, `description` = ".$db->quote($rows[$i]->kisa_icerik).", `image` = ".$db->quote($image)."*/ WHERE id = ".$row->id;
                $db->setQuery($query);
                $db->query();

                /*
                $query = "INSERT INTO #__lms_tests (`name` ,`published`,`catid`) VALUES (".$db->quote($rows[$i]->adi).", 1,1)";
                $db->setQuery($query);
                $db->query();    
                echo $db->geterrormsg()."<br />";
                */

            }
 
        }

        return $rows;

    }

    protected function lms_test_vs_membership() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT id, title from #__osemsc_acl";
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        for($i=0;$i<count($rows);$i++) {
            $query = "select id from #__lms_tests WHERE name like '%".$rows[$i]->title."%'";

            $db->setQuery($query);
            $row = $db->loadObject();

            $query = "INSERT INTO #__flexpaper_quiz (`test_id` ,`membership_list_id`) VALUES (".$row->id.", ".$rows[$i]->id.")";
            $db->setQuery($query);
            $db->query();    
            echo $db->geterrormsg()."<br />";

        }
die;
        // echo "<pre>";
        // print_r($row); die;

    }

    protected function grabCertificates() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        // Select some fields
        $query = 'SELECT a.*, b.id as osemsc_acl_id, c.id as users_id, e.test_id as tid 

                FROM sertifikalar as a, #__osemsc_acl as b, #__users as c , siparisler as d, #__flexpaper_quiz as e

                WHERE b.old_id = a.egitim_id AND a.fatura_no = d.id AND a.kullanici_id = c.old_id AND e.membership_list_id = b.id';

        $db->setQuery($query);
        $rows = $db->loadObjectList();

         // echo "<pre>";
         // print_r($rows); die;
        

        for($i=0;$i<count($rows);$i++) {
            $query = "INSERT INTO #__osemsc_order (`user_id` ,`order_status`) VALUES (".$rows[$i]->users_id.", 'confirmed')";
            $db->setQuery($query);
            $db->query();

            $insert_id = $db->insertid(); 

            if($insert_id) {
                $query = "INSERT INTO #__osemsc_order_item (`order_id` ,`entry_id`) VALUES (".$insert_id.", ".$rows[$i]->osemsc_acl_id.")";
                $db->setQuery($query);
                $db->query();

                $query = "INSERT INTO #__flexpaper_certificate (`cert_id` ,`user_id`, `tid`) VALUES (".$db->quote($rows[$i]->sertifika_no).", ".$rows[$i]->users_id.", ".$rows[$i]->tid.")";
                $db->setQuery($query);
                $db->query();

            }   

        }

        $this->insertCertIntoResults();

    }


    public function insertCertIntoResults() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT a.*, b.tarih FROM #__flexpaper_certificate as a, sertifikalar as b WHERE a.cert_id = b.sertifika_no";
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        for($i=0;$i<count($rows);$i++) {
            $query = "INSERT INTO #__lms_results (`userid`,`tid`, `date_created`) VALUES (".$rows[$i]->user_id.", ".$rows[$i]->tid.", '".$rows[$i]->tarih."')";
            $db->setQuery($query);
            $db->query();
            echo $db->geterrormsg(); 
        }

        // echo "<pre>";
        // print_r($rows); die;

    }

    public function CopyQuestions() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT * FROM #__lms_questions";
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        for($i=0;$i<count($rows);$i++) {
            $query = "UPDATE #__lms_questions SET a1= ".$db->quote($rows[$i]->a0)." WHERE id=".$rows[$i]->id." AND qtype = 'sa'";
            $db->setQuery($query);
            $db->query();
            echo $db->geterrormsg();
        }
    }

    public function UpdateRegistrationDate() {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT id, tarih FROM kullanicilar";
        $db->setQuery($query);
        $rows = $db->loadObjectList();

//        echo "<pre>";
//        print_r($rows); die;


        for($i=0;$i<count($rows);$i++) {
            $query = "UPDATE #__users SET registerDate= ".$db->quote($rows[$i]->tarih)." WHERE old_id=".$rows[$i]->id;
            $db->setQuery($query);
            $db->query();
//            echo $db->geterrormsg();
        }



    }


    public function getItem() {

        /* for tests */
        //echo "<pre>";

//        $this->update_certificates_table_with_date_column();
//        die;

        /* end */

        $user_id = JRequest::getVar('id');

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        // Select some fields
        $query = 'SELECT a.* FROM #__users as a WHERE a.id ='.$user_id;

        $db->setQuery($query);
        $row = $db->loadObject();

        $row->certificates = $this->getUserCertificates();

//         echo "<pre>";
//         print_r($row); die;

        $row->CreatedCertificates = $this->getCreatedCertificates();

//        echo "<pre>";
//        print_r($row); die;

        return $row;
    }


    public function getCreatedCertificates() {

        $user_id = JRequest::getVar('id');

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT a.*, b.*, c.cert_id, c.date_created FROM #__lms_results as a, #__flexpaper_quiz as b, #__flexpaper_certificate as c"
                ." LEFT JOIN #__flexpaper_quiz_results as d ON d.user_id = ".$user_id." AND d.tid = c.tid"
                ." WHERE c.user_id=".$user_id
                ." AND c.tid = b.test_id"
                ." AND c.user_id = a.userid"
                ." AND c.tid = a.tid";
                //." GROUP BY c.cert_id"
        ;
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $new_array = array();
        for($i=0;$i<count($rows);$i++) {
            $new_array[$rows[$i]->membership_list_id] = $rows[$i];
        }

        return $new_array;
    }

    public function getUserCertificates() {

        $user_id = JRequest::getVar('id');

//        echo $user_id; die;

//        $db = JFactory::getDBO();
//        $query = $db->getQuery(true);
//
//        $query = "SELECT c.* FROM #__flexpaper_certificate as a, #__flexpaper_quiz as b, #__osemsc_acl as c WHERE a.user_id = ".$user_id." and b.test_id = a.tid and c.id = b.membership_list_id";
//        $db->setQuery($query);
//        $rows = $db->loadObjectList();

        JLoader::import( 'courses', JPATH_SITE . DS . 'components' . DS . 'com_flexpaper' . DS . 'models' );
        $courses_model = JModel::getInstance( 'courses', 'flexpaperModel' );
        $rows = $courses_model->getItems($user_id,'mydocs', false, false);

        //$created_certificates = $this->getCreatedCertificates();

//        echo "<pre>";
//        print_r($rows); die;

//        echo "<pre>";
//        print_r($created_certificates); die;

//        $db = JFactory::getDBO();
//        $query = $db->getQuery(true);
//
//        $query = "SELECT a.id, a.cert_id as certificate_name, b.name, b.id as cert_id FROM #__flexpaper_certificate as a, #__lms_tests as b  where a.user_id=".$user_id." AND a.tid = b.id";
//        $db->setQuery($query);
//        $rows = $db->loadObjectList();

       return $rows;
   }


}
