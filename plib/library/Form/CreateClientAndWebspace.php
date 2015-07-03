<?php
// Copyright 1999-2015. Parallels IP Holdings GmbH.

class Modules_ApiUsage_Form_CreateClientAndWebspace extends pm_Form_Simple
{

    public function init()
    {
        $this->addElement('simpleText', 'client', array(
            'label' => $this->lmsg('fieldClient'),
            'value' => 'Client',
        ));

        $this->addElement('text', 'domain', array(
            'label' => $this->lmsg('fieldDomain'),
            'value' => 'client.tld',
        ));

        $this->addControlButtons(array(
            'cancelHidden' => true,
        ));
    }

    public function process()
    {
        $values = $this->getValues();
        $domainName = $values['domain'];

        if ($this->_isClientExist('client')) {
            throw new pm_Exception('Client with login "client" is already exist.');
        }

        $apiRequest = <<<APICALL
<customer>
    <add>
        <gen_info>
            <pname>Client</pname>
            <login>client</login>
            <passwd>testpassword</passwd>
        </gen_info>
    </add>
</customer>
APICALL;
        $apiResponse = pm_ApiRpc::getService()->call($apiRequest);
        $clientId = $apiResponse->customer->add->result->id;

        $ipAddress = $this->_getIpAddress();

        $apiRequest = <<<APICALL
<webspace>
    <add>
        <gen_setup>
            <owner-id>$clientId</owner-id>
            <name>$domainName</name>
            <ip_address>$ipAddress</ip_address>
        </gen_setup>
        <hosting>
            <vrt_hst>
                <property>
                    <name>ftp_login</name>
                    <value>testlogin</value>
                </property>
                <property>
                    <name>ftp_password</name>
                    <value>testpassword</value>
                </property>
                <ip_address>$ipAddress</ip_address>
            </vrt_hst>
        </hosting>
    </add>
</webspace>
APICALL;
        pm_ApiRpc::getService()->call($apiRequest);
    }

    private function _getIpAddress()
    {
        $apiResponse = pm_ApiRpc::getService()->call('<ip><get/></ip>');
        return $apiResponse->ip->get->result->addresses->ip_info->ip_address;
    }

    private function _isClientExist($login)
    {
        $apiRequest = <<<APICALL
<customer>
    <get>
        <filter>
            <login>$login</login>
        </filter>
        <dataset>
            <gen_info/>
        </dataset>
    </get>
</customer>
APICALL;

        $apiResponse = pm_ApiRpc::getService()->call($apiRequest);
        $result = $apiResponse->customer->get->result;
        return $result->status && ('ok' == $result->status);
    }
}
