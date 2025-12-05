// send_notifications.php - uses Twilio (composer require twilio/sdk)
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;

$sid = 'TWILIO_SID';
$token = 'TWILIO_TOKEN';
$from = 'whatsapp:+1415xxxxxxx'; // your Twilio WhatsApp number

$client = new Client($sid,$token);

// $absentees is array of ['regdno','name','phone']
foreach($absentees as $a){
  $to = 'whatsapp:+' . preg_replace('/\D/','',$a['phone']);
  $msg = "DEAR STUDENT {$a['name']} WITH {$a['regdno']} ABSENT FOR SESSION(s): 1"; // build list as needed
  $client->messages->create($to, ['from'=>$from, 'body'=>$msg]);
}
