<?php
/**
 * Function below can be used directly in your code, Just replace the capitalized values with the actual ones
 * @param string $filename
 * @param string $subject
 * @param string $message
 * @param int $faxnumber
 * @return string 
 */
function sendFax($filename, $subject, $message, $faxnumber) {
    try {
        $soap = new SoapClient('https://wsf2.metrofax.com/webservice.asmx?wsdl', array('trace' => 1, 'exception' => 0));
        $attach_response = $soap->__call('UploadAttachment', array(
            'UploadAttachment' => array(
                'loginId' => 'LOGIN',
                'password' => 'PASS',
                'fileName'=>$filename,
                'base64EncodedString'=>base64_encode($message)
            )
        ));

        if (!empty($attach_response->UploadAttachmentResult->ResultString)) {
            $upload_id = (string)$attach_response->UploadAttachmentResult->ResultString;

            $send_response = $soap->__call('SendFax', array(
                'SendFax' => array(
                    'loginId' => 'LOGIN',
                    'password' => 'PASS',
                    'subject' => $subject,
                    'message' => '',
                    'recipients' => array(
                        'FaxRecipient' => array(
                            'FaxNumber' => $faxnumber,
                            'Name' => '',
                            'Company' => '',
                            'VoiceNumber' => ''
                        )
                    ),
                    'attachments' => array(
                        'FileRef' => array(
                            'Id' => $upload_id
                        )
                    ),
		    'coverPageInfo' => array(
                      			'CoverPage' => 'COVERPAGESTRING',
                      			'FromName' => 'NAME',
                      			'FromEmail' => 'CONTACTEMAIL',
                      			'FromAddress' => 'ADDRESS',
                      			'FromPhone' => 'PHONE',
                      			'FromWebsite' => 'SITE',
                      			'FromFaxNumber' => 'FAXNUMBER',
                      			'NoteText' => 'NOTE',
                      			'Subject' => 'SUBJECT',
                      			'FromCompany' => 'COMPANY'
                      		    ),
		                )
            ));

            if(!empty($send_response->SendFaxResult->ResultString)) {
                if(preg_match('/.*Fax successfully sent.*/i', (string)$send_response->SendFaxResult->ResultString)) {
                    return 'Sent';
                }
                else {
                    return (string)$send_response->SendFaxResult->ResultString;
                }
            }
        }
    } catch(SoapException $e) {
        return $e->getMessage();
    }
}
