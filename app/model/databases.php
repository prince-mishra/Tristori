<?php

  class account_authentication extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_account_authentication", array(
				"id" => "int",
				"user_id" => "int",
				"account_id" => "int",
				"user_type" => "varchar_50"
      ));
    }
  }

  class accounts extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_accounts", array(
				"id" => "int",
				"name" => "varchar_50",
				"invites_left" => "int",
				"account_type" => "varchar_50",
				"registeration_date" => "varchar_50",
				"expiry_date" => "varchar_50",
				"invites_total" => "int"
      ));
    }
  }

  class activity extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_activity", array(
				"id" => "int",
				"user_id" => "int",
				"user_type" => "varchar_50",
				"account_id" => "int",
				"page_url" => "varchar_100",
				"message" => "varchar_500",
				"session_id" => "varchar_50",
				"timestamp" => "varchar_100"
      ));
    }
  }

  class authentication extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_authentication", array(
				"id" => "int",
				"username" => "varchar_128",
				"password" => "varchar_128",
				"display_name" => "varchar_50",
				"email_address" => "varchar_100",
				"timestamp" => "varchar_50",
				"account_hash" => "varchar_128"
      ));
    }
  }

  class bullet_list extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_bullet_list", array(
				"id" => "int",
				"bullet_text" => "varchar_50",
				"bullet_level" => "int",
				"parent_id" => "int",
				"display_type" => "varchar_50",
				"prefix" => "varchar_50",
				"suffix" => "varchar_50",
				"bullet_type" => "varchar_50",
				"bullet_order" => "int"
      ));
    }
  }

  class college extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_college", array(
				"id" => "int",
				"account_id" => "int",
				"full_name" => "varchar_100",
				"registration_date" => "varchar_50",
				"address_line_1" => "varchar_250",
				"address_line_2" => "varchar_250",
				"address_country" => "varchar_20",
				"address_state" => "varchar_20",
				"address_city" => "varchar_20",
				"address_zip_code" => "varchar_10",
				"hash_key" => "varchar_100",
				"message" => "text",
				"message_header" => "varchar_255",
				"contact_number" => "varchar_32",
				"email_address" => "varchar_128",
				"fax_number" => "varchar_32",
				"website" => "varchar_63",
				"recruiter_invites_total" => "int",
				"recruiter_invites_left" => "int"
      ));
    }
  }

  class company extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_company", array(
				"id" => "int",
				"account_id" => "int",
				"full_name" => "varchar_100",
				"registration_date" => "varchar_50",
				"address_line_1" => "varchar_250",
				"address_line_2" => "varchar_250",
				"address_country" => "varchar_20",
				"address_state" => "varchar_20",
				"address_city" => "varchar_32",
				"address_zip_code" => "varchar_10",
				"access_level" => "varchar_32"
      ));
    }
  }

  class company_college extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_company_college", array(
				"id" => "int",
				"college_id" => "int",
				"company_id" => "int",
				"timestamp" => "int"
      ));
    }
  }

  class invites extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_invites", array(
				"id" => "int",
				"account_id" => "int",
				"user_id" => "int",
				"hash_key" => "varchar_500",
				"invite_type" => "varchar_50",
				"invited_id" => "int",
				"invited_first_name" => "varchar_100",
				"invited_middle_name" => "varchar_100",
				"invited_last_name" => "varchar_100",
				"invited_year" => "varchar_100",
				"invited_mail_id" => "varchar_100",
				"status" => "varchar_50",
				"timestamp" => "varchar_100"
      ));
    }
  }

  class job_meta extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_job_meta", array(
				"id" => "int",
				"job_profile_id" => "int",
				"job_institute_id" => "int",
				"key" => "varchar_63",
				"value" => "varchar_1023"
      ));
    }
  }

  class job_profiles extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_job_profiles", array(
				"id" => "int",
				"timestamp" => "varchar_50",
				"account_id" => "int",
				"unique_id" => "int",
				"job_code" => "varchar_32",
				"job_title" => "varchar_50",
				"job_type" => "varchar_50",
				"status" => "varchar_50"
      ));
    }
  }

  class job_profle_search_criteria extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_job_profle_search_criteria", array(
				"id" => "int",
				"job_profile_id" => "int",
				"parent_id" => "int",
				"key" => "varchar_50",
				"value" => "varchar_50",
				"header_type" => "varchar_50",
				"order" => "varchar_50",
				"is_visible" => "tinyint_1"
      ));
    }
  }

  class list_company extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_list_company", array(
				"id" => "int",
				"company_name" => "varchar_100",
				"domain" => "varchar_50",
				"strength" => "varchar_50",
				"industry_type" => "varchar_50",
				"address_1" => "varchar_500",
				"address_2" => "varchar_500",
				"city_id" => "int",
				"district_id" => "int",
				"state_id" => "int",
				"country" => "varchar_50",
				"zip_code" => "varchar_20"
      ));
    }
  }

  class list_institute extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_list_institute", array(
				"id" => "int",
				"institute_name" => "varchar_100",
				"university_id" => "int",
				"address_1" => "varchar_500",
				"address_2" => "varchar_500",
				"ciry_id" => "int",
				"district_id" => "int",
				"state_id" => "int",
				"country" => "varchar_20",
				"zip_code" => "varchar_20"
      ));
    }
  }

  class list_university extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_list_university", array(
				"id" => "int",
				"university_name" => "varchar_100",
				"address_1" => "varchar_500",
				"address_2" => "varchar_500",
				"city_id" => "int",
				"district_id" => "int",
				"state_id" => "int",
				"country" => "varchar_50",
				"zip_code" => "varchar_20"
      ));
    }
  }

  class lists extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_lists", array(
				"id" => "int",
				"list_type" => "varchar_50",
				"name" => "varchar_50",
				"order" => "varchar_50",
				"parent_id" => "int",
				"status" => "varchar_50"
      ));
    }
  }

  class shortlisted_institutes extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_shortlisted_institutes", array(
				"id" => "int",
				"institute_id" => "int",
				"job_profile_id" => "int",
				"potential_talent" => "int",
				"contact_date" => "int",
				"pc_response" => "varchar_32",
				"pc_response_timestamp" => "int",
				"interested_talent" => "int",
				"interested_talent_timestamp" => "int",
				"shortlisted_talent" => "int",
				"interview_date" => "int",
				"selected_talent" => "int",
				"accepted_talent" => "int",
				"next_activity" => "varchar_128"
      ));
    }
  }

  class shortlisted_student extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_shortlisted_student", array(
				"id" => "int",
				"student_id" => "int",
				"institute_account_id" => "int",
				"job_profile_id" => "int",
				"status" => "varchar_50",
				"timestamp" => "varchar_100"
      ));
    }
  }

  class student extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_student", array(
				"id" => "int",
				"full_name" => "varchar_100",
				"birth_date" => "varchar_50",
				"registration_date" => "varchar_50",
				"email_id" => "varchar_50",
				"address_line_1" => "varchar_250",
				"address_line_2" => "varchar_250",
				"address_country" => "varchar_20",
				"profile_image" => "varchar_50",
				"address_state" => "varchar_20",
				"address_city" => "varchar_20",
				"address_zip_code" => "varchar_10",
				"contact_number" => "varchar_20",
				"mobile_number" => "varchar_20",
				"user_name" => "varchar_50",
				"resume_url" => "varchar_100",
				"institute_invite_id" => "int",
				"account_id" => "int",
				"placement_status" => "varchar_50",
				"resume_completion" => "varchar_50",
				"education_order" => "int",
				"experiance_order" => "int",
				"additional_info_order" => "int",
				"gender" => "varchar_16"
      ));
    }
  }

  class student_bullets extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_student_bullets", array(
				"id" => "int",
				"student_id" => "int",
				"parent_id" => "int",
				"parent_type" => "varchar_50",
				"bullet_id" => "int",
				"bullet_text" => "varchar_100",
				"order" => "int",
				"is_visible" => "tinyint_1"
      ));
    }
  }

  class student_resume extends Model {
    public function __construct($database) {
      $this->construct($database, "cv_student_resume", array(
				"id" => "int",
				"student_id" => "int",
				"key" => "varchar_50",
				"value" => "varchar_150",
				"visible" => "tinyint_1",
				"header_type" => "varchar_50",
				"parent_id" => "int",
				"order" => "int"
      ));
    }
  }

  class gtx_users extends Model {
    public function __construct($database) {
      $this->construct($database, "gtx_users", array(
				"id" => "int",
				"username" => "varchar_64",
				"password" => "varchar_64",
				"email" => "varchar_64",
				"lastlogin" => "varchar_32",
				"ip" => "varchar_32",
				"name" => "varchar_64",
				"hash" => "varchar_64"
      ));
    }
  }

?>