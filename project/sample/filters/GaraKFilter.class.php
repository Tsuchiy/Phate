<?php
class GaraKFilter extends PhateOutputFilterBase
{
    public function execute(&$contents) {
        $emoji = HTML_Emoji::getInstance();
        $contents = $emoji->convertCarrier($contents);
    }
}
