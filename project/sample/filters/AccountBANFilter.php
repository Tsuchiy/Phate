<?php
class AccountBANFilter extends PhateInputFilterBase
{
    public function execute() {
        if ($userId = DwHttpRequest::getHeaderParam('User-Id')) {
        }
        return;
    }
}
