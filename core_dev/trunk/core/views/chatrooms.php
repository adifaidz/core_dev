<?php

// chatroom moderation

//TODO: ability to LOCK a chatroom so it cant be used
//TODO: ability to configure a chatroom to allow anonymous users

$session->requireSuperAdmin();

switch ($this->owner) {
case 'list':
    echo '<h2>Existing chatrooms</h2>';
    echo '<br/>';

    $list = ChatRoom::getList();

    foreach ($list as $cr)
        echo ahref('coredev/view/chatrooms/edit/'.$cr->id, $cr->name).'<br/>';

    echo '<br/>';
    echo '&raquo; '.ahref('coredev/view/chatrooms/new', 'New chatroom');
    break;

case 'edit':
    // child = room id
    function editHandler($p)
    {
        $o = new ChatRoom();
        $o->id = $p['roomid'];
        $o->name = trim($p['name']);
        ChatRoom::store($o);

        js_redirect('coredev/view/chatrooms/list');
    }

    $o = ChatRoom::get($this->child);
    echo '<h2>Edit chatroom '.$o->name.'</h2>';

    $x = new XhtmlForm();
    $x->addHidden('roomid', $o->id); //XXX haxx
    $x->addInput('name', 'Name', $o->name, 40);
    $x->addSubmit('Save');
    $x->setHandler('editHandler');
    echo $x->render();
    break;

case 'new':
    // child = room id
    function createHandler($p)
    {
        $o = new ChatRoom();
        $o->name = trim($p['name']);
        ChatRoom::store($o);

        js_redirect('coredev/view/chatrooms/list');
    }

    echo '<h2>Create new chatroom</h2>';

    $x = new XhtmlForm();
    $x->addInput('name', 'Name');
    $x->addSubmit('Create');
    $x->setHandler('createHandler');
    echo $x->render();
    break;

default:
    echo 'No handler for view '.$this->owner;
}

?>
