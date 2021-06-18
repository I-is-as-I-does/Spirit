<?php


//create <datagrid>
$secretHash = password_hash($secret, PASSWORD_BCRYPT);
$indata = implode('|', [date('Ymd'), $accessUrl, $username, $secretHash]);</datagrid>


///
 private function checkPassportDate($date, $minDate = null)
 {
     if (strlen($date) == 8) {
         $month = substr($date, 4, 2);
         $day = substr($date, -2);
         $year = substr($date, 0, 4);
         if (checkdate($month, $day, $year) &&
             (($minDate === null && !Jack::Time()->isFuture($date)) ||
                 (!empty($minDate) && Jack::Time()->isInRange($date, $minDate)))) {
             return true;
         }
     }
     return false;
 }

 protected function checkData($decrypt, $secret, $accessUrl, $username, $minDate = null)
 {
     $pieces = explode('|', $decrypt); // @doc: ['YYYYMMDD','url', 'username', 'secrethash'];

     switch (true) {
         case (count($pieces) !== 4):
             $err = 'invalid-data-model';
             break;
         case ($accessUrl != $pieces[1]):
             $err = 'invalid-url';
             break;
         case ($username != $pieces[2]):
             $err = 'invalid-username';
             break;
         case (!password_verify($secret, $pieces[3])):
             $err = 'invalid-secret';
             break;
         case (!$this->checkPassportDate($pieces[0], $minDate)):
             $err = 'invalid-date';
             break;
         default:
             return true;
     }
     $this->Spirit->record($err);
     return false;
 }