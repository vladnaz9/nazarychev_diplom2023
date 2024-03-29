<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;

    class users
    {

        private $userRepo;
        private $tpl;
        private $profLevelRepo;

        public function __construct()
        {
            $this->tpl = new core\template();
            $this->userRepo = new repositories\users();
            $this->profLevelRepo = new repositories\profLevel();
        }

        //GET
        public function getProfilePicture($id)
        {
            return $this->userRepo->getProfilePicture($id);
        }

        public function getNumberOfUsers()
        {
            return $this->userRepo->getNumberOfUsers();
        }

        public function getAll()
        {
            return $this->userRepo->getAll();
        }

        public function getUser($id)
        {
            return $this->userRepo->getUser($id);
        }


        //POST
        public function setProfilePicture($photo, $id)
        {
            $this->userRepo->setPicture($photo, $id);
        }

        public function updateUserSettings($category, $setting, $value)
        {

            $filteredInput = filter_var($setting, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH);
            $filteredValue = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH);

            $_SESSION['userdata']['settings'][$category][$filteredInput] =  $filteredValue;

            $serializeSettings = serialize($_SESSION['userdata']['settings']);

            return $this->userRepo->patchUser($_SESSION['userdata']['id'], array("settings" => $serializeSettings));
        }

        public function editUser($values, $id)
        {
            $this->profLevelRepo->deleteAllUserProfLevel($id);
            if ($values['projectroleId']) {
                foreach ($values['projectroleId'] as $key => $proficiencyInfo) {
                    $proficiencyInfo = explode('-', $proficiencyInfo);
                    $projectRoleId = $proficiencyInfo[0];
                    $profLevelId = $proficiencyInfo['1'];
                    $this->profLevelRepo->insertUserProfLevel($id, $projectRoleId, $profLevelId);
                    $values['projectroleId'][$key] = $projectRoleId;
                }
            }
            $this->userRepo->editUser($values, $id);
        }

        public function getUserProfLevel($userId)
        {
            $values = $this->profLevelRepo->getUserProfLevel($userId);
            $result = [];
            foreach ($values as $profLevel) {
                $result[] = $profLevel['projectroleId']. '-' . $profLevel['proficiencyLevelId'];
            }

            return $result;
        }

    }
}
