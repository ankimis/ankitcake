<?php
App::uses('AppController', 'Controller');


class UsersController extends AppController
{



    public $helpers = array('Js');
    public $components = array('Session', 'flash');
    public function beforeFilter()
    {
        parent::beforeFilter();
        // Allow users to register and logout.
        $this->Auth->allow('add', 'logout');
        $this->layout = 'login';
    }
    public function login()
    {
        if ($this->request->is('post'))
        // pr($this->request->data);exit;
        {
            if ($this->Auth->login()) {
                return $this->redirect($this->Auth->redirectUrl());
            }
            // $this->Flash->error(__('Invalid username or password, try again'));
            $this->Session->setFlash(__('You are not user please  create username and password.'));
        }
    }

    public function logout()
    {
        return $this->redirect($this->Auth->logout());
    }

    public function index()
    {
        $this->User->recursive = 0;
        $this->set('users', $this->paginate());
    }

    public function view($id = null)
    {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        $this->set('user', $this->User->findById($id));
    }

    public function add()
    {
        if ($this->request->is('post')) {
            $this->User->create();

            $check = ($this->request->data['User']['username']);
            if ($check) {
                $user = $this->User->findAllByUsername($check);
                if ($user) {
                    $this->Session->setFlash(__('Your username is Allready  saved.'));
                } else {
                    if ($this->User->save($check)) {
                        // $this->Flash->success(__('The user has been saved'));
                        $this->Session->setFlash(__('Your user has been saved.'));
                        return $this->redirect(array('action' => 'index'));
                    }
                    // $this->Flash->error(__('The user could not be saved. Please, try again.'));
                    $this->Session->setFlash(__('Your post has Not been saved.'));
                }
            }
        }
    }

    public function edit($id = null)
    {
        $this->User->id = $id;
        // pr($id);exit;
        //  $User = $this->User->findById($id);
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            // pr($this->request->data);exit;
            if ($this->User->save($this->request->data)) {
                // $this->Flash->success(__('The user has been saved'));
                $this->Session->setFlash(__('The user has been saved.'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        } else {
            $user = $this->User->findById($id);
            $username = $user['User']['username'];
            $id = $user['User']['id'];
            // pr($user);EXIT;
            $this->set('username', $username);
            $this->set('username', $id);
            $this->set('User', $user['User']);
        }
    }

    public function delete($id = null)
    {
        // Prior to 2.5 use
        // $this->request->onlyAllow('post');

        $this->request->allowMethod('post');

        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->User->delete()) {
            // $this->Flash->success(__('User deleted'));
            $this->Session->setFlash(__('User deleted.'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->Flash->error(__('User was not deleted'));
        return $this->redirect(array('action' => 'index'));
    }
    public function isAuthorized($user)
    {
        // All registered users can add posts
        if ($this->action === 'add') {
            return true;
        }

        // The owner of a post can edit and delete it
        if (in_array($this->action, array('edit', 'delete'))) {
            $studentId = (int) $this->request->params['pass'][0];
            if ($this->Student->isOwnedBy($studentId, $user['id'])) {
                return true;
            }
        }

        return parent::isAuthorized($user);
    }

    public function forget()
    {

        if ($this->request->is('post')) {
            $this->User->create();
            $check = ($this->request->data['User']['username']);
            pr($check);
            // pr($this->request->data['User']);
            // $user = $this->User->findAllByUsername('all', array('condition' => array('User. ' => $check),'order' => array('created' => 'asc')));
            $user = $this->User->findAllByUsername($check);
            pr($user);
            exit();
            $username = $user['User']['username'];
            $password = $user['User']['password'];
            $id = $user['User']['id'];
            //

            $this->set('username', $username);
            $this->set('password', $password);
            $this->set('id', $id);
            $this->set('User', $user['User']);
            return $this->redirect(array('action' => 'recreate', $user['User']['username']));

            if ($this->request->is(array('put'))) {
                $this->Student->create();
            }
        }
    }
    public function recreate($username)
    {
        // pr($id);exit;
        $forget = $this->User->find('first', array('User.username' => $username));
        $username = $forget['User']['username'];
        $password = $forget['User']['password'];
        $id = $forget['User']['id'];
        //

        $this->set('username', $username);
        $this->set('password', $password);
        $this->set('id', $id);
        $this->set('Users', $forget['User']);
        if ($this->request->is('post') || $this->request->is('put')) {
            $confirm = $this->request->data;
            pr($confirm);
            exit;
            if ($this->User->save($this->request->data)) {
                // $this->Flash->success(__('The user has been saved'));
                $this->Session->setFlash(__('The user has been saved.'));
                return $this->redirect(array('action' => 'login'));
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
            // pr($forget);
            // exit;
        }
    }
}