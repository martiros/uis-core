<?php
namespace UIS\Core\Exceptions;

class NotSupportedVersionException extends CatchableException
{
    public function getStatus()
    {
        return 'NOT_SUPPORTED_VERSION';
    }

    public function getMessageData()
    {
        return array(
            'title' => trans('uis_core.error.not_supported_version.title'),
            'body' => trans('uis_core.error.not_supported_version.body')
        );
    }

    public function getData()
    {
        $clientInfo = Jbf_ClientApp::getClientInfo();
        $applications = Core_Config::conf('jbf.applications');
        if (isset($applications[$clientInfo['device_type']])) {
            return array(
                'new_application_url' => $applications[$clientInfo['device_type']]
            );
        }
        return array();
    }
}
