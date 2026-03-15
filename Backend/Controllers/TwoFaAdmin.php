<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;

class TwoFaAdmin extends IndexAdmin
{
    public function fetch()
    {
        if ($this->request->method('post')) {
            $this->settings->set('rs__tfa__enable_users',        (int)(bool)$this->request->post('rs__tfa__enable_users'));
            $this->settings->set('rs__tfa__enable_admins',       (int)(bool)$this->request->post('rs__tfa__enable_admins'));
            $this->settings->set('rs__tfa__verify_registration', (int)(bool)$this->request->post('rs__tfa__verify_registration'));
            $this->settings->set('rs__tfa__passwordless_users',  (int)(bool)$this->request->post('rs__tfa__passwordless_users'));
            $this->settings->set('rs__tfa__passwordless_admins', (int)(bool)$this->request->post('rs__tfa__passwordless_admins'));
            $this->settings->set('rs__tfa__code_ttl',            max(1, min(60, (int)$this->request->post('rs__tfa__code_ttl'))));

            $this->design->assign('saved', true);
        }

        $this->design->assign('smtp_configured', !empty($this->settings->get('smtp_server')));

        $this->response->setContent($this->design->fetch('tfa_admin.tpl'));
    }
}
