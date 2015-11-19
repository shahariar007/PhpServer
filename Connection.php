<?php
require("PHPMailer/class.phpmailer.php");


class Connection
{
    private $server_name = "localhost";
    private $database_name = "mmdb";
    private $table_name = "tbl_user_registration";
    private $user_name = "root";
    private $main_table = "tbl_user_info";
    private $pass = "";
    private $db_helper;


    /**
     * Connection constructor.
     */
    public function __construct()
    {
        $this->db_helper = new mysqli($this->server_name, $this->user_name, $this->pass, $this->database_name);
        if (!$this->db_helper) {
            die("Fail To Connect");
        }
    }

    public function Registration($user_name, $user_email, $user_phone, $user_pass, $user_type)
    {
        $tbl_user_reg_check = $this->db_helper->query("select *from tbl_user_registration where user_email='$user_email' and user_validation_status=0")->fetch_assoc()['id'];
        $tbl_main_check = $this->db_helper->query("select id from tbl_user_info where user_email= '$user_email' ")->fetch_assoc()['id'];

        if (($tbl_main_check) != null || $tbl_user_reg_check > 0) {
            if (($tbl_main_check) != null) {
                //echo "already registered  this email address";
                return "already registered  this email address";

            } else {
                //echo "email address not varified";
                return "email address not varified";
            }


        } else {

            if (strcasecmp($user_type, "manual") == 0) {
                $generated_code = rand(50, 1000);
                $starttime = new DateTime();
                $start_time = $starttime->format('Y-m-d H:i:s');
                $endtime = new DateTime('+1 days');
                $end_time = $endtime->format('Y-m-d H:i:s');

                $user_validation_status = 0;
                $user_active_status = 0;

                $sql_insert = "INSERT INTO {$this->table_name} (user_name,user_email,user_phone,user_pass,user_type,start_time,end_time,user_validation_status,user_active_status,user_code) VALUES ('$user_name','$user_email','$user_phone','$user_pass','$user_type','$start_time','$end_time',$user_validation_status,$user_active_status,$generated_code);";
                $result = $this->db_helper->query($sql_insert);
                if ($result) {
                    $n = new Connection();
                    $n->MailTransfer($user_email, $user_name, $generated_code);
                    return "complete";
                } else echo $this->db_helper->error;
            } elseif (strcasecmp($user_type, "facebook") == 0 || strcasecmp($user_type, "google_plus") == 0 || strcasecmp($user_type, "twitter") == 0) {
                echo $user_name;
                $sql_insert = "INSERT INTO {$this->main_table} (user_name,user_email,user_phone,user_pass,user_type) VALUES ('$user_name','$user_email','$user_phone','$user_pass','$user_type');";
                $result = $this->db_helper->query($sql_insert);
                if ($result) {

                    return "registration complete via" . $user_type;
                } else return "fail registration complete via" . $user_type;

            }

        }
    }

    public function Verification($mailaddress, $mailcode)
    {
        $sqlveri = "SELECT user_code from {$this->table_name} where user_email='$mailaddress'";
        $result = $this->db_helper->query($sqlveri);
        $mCode = $result->fetch_assoc()['user_code'];

        if ($mailcode == $mCode) {
            $sql_update = "UPDATE {$this->table_name} SET user_validation_status=1 WHERE user_email= '$mailaddress'";
            $update_result = $this->db_helper->query($sql_update);
            if ($update_result) {
                $getdataqueary = "SELECT user_name,user_email,user_phone,user_pass,user_type FROM tbl_user_registration WHERE user_email='$mailaddress'";
                $getdata = $this->db_helper->query($getdataqueary)->fetch_array();
                //print_r($getdata);die;

                $insert_queary = "INSERT INTO tbl_user_info (user_name,user_email,user_phone,user_pass,user_type) VALUES ('$getdata[0]','$getdata[1]','$getdata[2]','$getdata[3]','$getdata[4]');";
                $insertresult = $this->db_helper->query($insert_queary);
                if ($insertresult) {
                    $sql_delete = "DELETE FROM {$this->table_name} WHERE user_email= '$mailaddress'";
                    $deleteresult = $this->db_helper->query($sql_delete);
                    if ($deleteresult) {
                        $sql = "SELECT * FROM {$this->main_table} WHERE user_email='$mailaddress';";
                        $result = $this->db_helper->query($sql)->fetch_assoc();
                        return json_encode($result);
                    } else return $this->db_helper->error;

                } else  return $this->db_helper->error;
            } else return $this->db_helper->error;

        } else return $this->db_helper->error;
    }


    public function Getdata()
    {
        $sql = "SELECT * FROM {$this->main_table} ; ";
        $result = $this->db_helper->query($sql);
        $data = array();
        while ($rds = $result->fetch_assoc()) {
            array_push($data, $rds);
        }
        return json_encode($data);
    }


    public function MailTransfer($mailSendAddress, $MailName, $body)
    {
        $mail = new PHPmailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;

        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->Username = "shuvo11101010@gmail.com";
        $mail->Password = "shuvo01937092169";
        $mail->SMTPSecure = 'ssl';
        $mail->SetFrom('shuvo11101010@gmail.com', 'Mortuza');
        $mail->Subject = "Confirmation message  from New Main Zone ";
        $mail->AltBody = "Any message.";
        $mail->MsgHTML($body);

        $address = $mailSendAddress;
        $mail->AddAddress($address, $MailName);
        if (!$mail->Send()) {
            echo $mail->ErrorInfo;
            return 0;
        } else {
            return 1;
        }
    }

}

?>