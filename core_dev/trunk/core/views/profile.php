<?php
/**
 * Default view for a user profile
 */

$session->requireLoggedIn();

$user_id = $session->id;

if ($this->owner)
    $user_id = $this->owner;


$user = User::get($user_id);

if (!$user)
    die('ECHKKP');

echo '<h1>Profile for '.$user->name.'</h1>';

echo 'Last active: '.ago($user->time_last_active).'<br/>';
echo 'Is online: '. ( UserHandler::isOnline($user_id) ? 'YES' : 'NO').'<br/>';
echo 'User level: '.UserHandler::getUserLevel($user_id).'<br/>';

$gender_id = UserSetting::get($user_id, 'gender');
$gender = Settings::getById($gender_id);
echo 'Gender: '.$gender.'<br/>';


echo 'E-mail: '.UserSetting::get($user_id, 'email').'<br/>';
echo 'Want ads?: '.UserSetting::get($user_id, 'want_ads').'<br/>';

echo 'Presentation: '.UserSetting::get($user_id, 'presentation').'<br/>';

$pic_id = UserSetting::get($user_id, 'picture');
if ($pic_id)
    echo 'Profile picture: '.showThumb($pic_id, 'Profilbild', 50, 50).'<br/>';



echo '<br/>';

if ($session->id && $user_id != $session->id)
    echo '&raquo; '.ahref('coredev/view/profile_messages/send/'.$user_id, 'Send message').'<br/>';

echo '&raquo; '.ahref('coredev/view/profile_guestbook/'.$user_id, 'Guestbook').'<br/>';

if ($session->id && $user_id == $session->id)
    echo '&raquo; '.ahref('coredev/view/profile_edit', 'Edit profile').'<br/>';

?>
