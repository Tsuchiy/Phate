<?php
class ThrownException extends PhateThrownExceptionBase
{
    public function execute(Exception $e)
    {
        if ($e instanceof PhateUnauthorizedException) {
            PhateHttpResponseHeader::setHttpStatus(PhateHttpResponseHeader::HTTP_UNAUTHORIZED);
            
        } else {
            PhateHttpResponseHeader::setHttpStatus(PhateHttpResponseHeader::HTTP_INTERNAL_SERVER_ERROR);
        }
        PhateHttpResponseHeader::sendResponseHeader();
        if (PhateCore::isDebug()) {
            var_dump($e);
        }
        exit();
    }
}
