<?php 

include 'vendor/autoload.php';

include 'header.php';
require 'aws/aws-autoloader.php';

error_reporting(0);
ini_set('display_errors', 'Off');
$p = $_POST;

$keyid = $p['keyID'];
$secret = $p['keySecret'];

$sharedConfig = [
    'region'  => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
        'key'    => $keyid,
        'secret' => $secret,
    ],
];


$sdk = new Aws\Sdk($sharedConfig);
$ec2Client = $sdk->createEC2();
$result2 = $ec2Client->describeInstances();

// Create the key pair
$bytes = rand(1000,9999);
$keyPairName = 'cisobox-created-keypair-'.strval($bytes);
$key = $ec2Client->createKeyPair(array(
    'KeyName' => $keyPairName
));

$keyPairName2 = 'cisobox-vault-keypair-'.strval($bytes);
$vaultKey = $ec2Client->createKeyPair(array(
    'KeyName' => $keyPairName2
));

// Create the security group
$securityGroupName = 'cisbox-security-group-'.$bytes;
$securityGroupName2 = 'cisbox-security-group-vault-'.$bytes;

$result = $ec2Client->createSecurityGroup(array(
    'GroupName'   => $securityGroupName,
    'Description' => 'Basic server security'
));
$result = $ec2Client->createSecurityGroup(array(
    'GroupName'   => $securityGroupName2,
    'Description' => 'Vault server security'
));

// Get the security group ID (optional)
$securityGroupId = $result->get('GroupId');

// Set ingress rules for the security group
$ec2Client->authorizeSecurityGroupIngress(array(
    'GroupName'     => $securityGroupName,
    'IpPermissions' => array(
        array(
            'IpProtocol' => 'tcp',
            'FromPort'   => 80,
            'ToPort'     => 80,
            'IpRanges'   => array(
                array('CidrIp' => '0.0.0.0/0')
            ),
        ),
        array(
            'IpProtocol' => 'tcp',
            'FromPort'   => 443,
            'ToPort'     => 443,
            'IpRanges'   => array(
                array('CidrIp' => '0.0.0.0/0')
            ),
        ),
        array(
            'IpProtocol' => 'udp',
            'FromPort'   => 1514,
            'ToPort'     => 1514,
            'IpRanges'   => array(
                array('CidrIp' => '0.0.0.0/0')
            ),
        ),
        array(
            'IpProtocol' => 'tcp',
            'FromPort'   => 22,
            'ToPort'     => 22,
            'IpRanges'   => array(
                array('CidrIp' => '0.0.0.0/0')
            ),
        )
    )
));

$ec2Client->authorizeSecurityGroupIngress(array(
    'GroupName'     => $securityGroupName2,
    'IpPermissions' => array(
        array(
            'IpProtocol' => 'tcp',
            'FromPort'   => 22,
            'ToPort'     => 22,
            'IpRanges'   => array(
                array('CidrIp' => '0.0.0.0/0')
            ),
        )
    )
));

// Launch an instance with the key pair and security group

//hiawatha 1 	ami-7454cc0e
$hia1 = $ec2Client->runInstances(array(
    'ImageId'        => 'ami-7454cc0e',
    'MinCount'       => 1,
    'MaxCount'       => 1,
    'InstanceType'   => 't2.micro',
    'KeyName'        => $keyPairName,
    'SecurityGroups' => array($securityGroupName),
));
//hiawatha 2 ami-e653cb9c
$hia2 = $ec2Client->runInstances(array(
    'ImageId'        => 'ami-e653cb9c',
    'MinCount'       => 1,
    'MaxCount'       => 1,
    'InstanceType'   => 't2.micro',
    'KeyName'        => $keyPairName,
    'SecurityGroups' => array($securityGroupName),
));
//haproxy ami-8c56cbf6
$hap = $ec2Client->runInstances(array(
    'ImageId'        => 'ami-8c56cbf6',
    'MinCount'       => 1,
    'MaxCount'       => 1,
    'InstanceType'   => 't2.micro',
    'KeyName'        => $keyPairName,
    'SecurityGroups' => array($securityGroupName),
));
//ossec 	ami-c2294eb8
$ossec = $ec2Client->runInstances(array(
    'ImageId'        => 'ami-c2294eb8',
    'MinCount'       => 1,
    'MaxCount'       => 1,
    'InstanceType'   => 't2.micro',
    'KeyName'        => $keyPairName,
    'SecurityGroups' => array($securityGroupName),
));
//,

//gogs ami-0951cc73 ami-0951cc73
$gogs = $ec2Client->runInstances(array(
    'ImageId'        => 'ami-0951cc73',
    'MinCount'       => 1,
    'MaxCount'       => 1,
    'InstanceType'   => 't2.micro',
    'KeyName'        => $keyPairName,
    'SecurityGroups' => array($securityGroupName),
));

//vault ami-012d4a7b
$vault = $ec2Client->runInstances(array(
    'ImageId'        => 'ami-012d4a7b',
    'MinCount'       => 1,
    'MaxCount'       => 1,
    'InstanceType'   => 't2.micro',
    'KeyName'        => $keyPairName2,
    'SecurityGroups' => array($securityGroupName2),
));


/*
echo '<pre>';
var_dump($ec2instances);
echo '</pre><br><br>';
*/

$h1['id'] = $hia1['Instances'][0]['InstanceId'];
$h2['id'] = $hia2['Instances'][0]['InstanceId'];
$hap['id'] = $hap['Instances'][0]['InstanceId'];
$ossec['id'] = $ossec['Instances'][0]['InstanceId'];
$gogs['id'] = $gogs['Instances'][0]['InstanceId'];
$vault['id'] = $vault['Instances'][0]['InstanceId'];


//$saveKeyLocation = getenv('HOME') . "/.ssh/{$keyPairName}.pem";
//file_put_contents($saveKeyLocation, $key['keyMaterial']);
// Update the key's permissions so it can be used with SSH
//chmod($saveKeyLocation, 0600);


echo '
        <div class="container" style="margin-top:15px;">
            <div class="box">
                <div class="columns">
                    <div class="column">
                        <progress id="progressBar" class="progress is-primary" value="15" max="100">30%</progress>
                        <p><i id="spinnyThing" class="fa fa-cog fa-spin"></i> <span id="currentAction" style="margin-left:5px;">Deploying your servers </span></p>

                    </div>
                </div>
            </div>
';


?>
<script>
    setTimeout(function() { 
        document.getElementById("progressBar").value = "40";
        document.getElementById("currentAction").innerHTML = "Letting servers boot";
    }, 4000);
</script>
            <div class="columns">
                    <div class="column is-one-third">
                        <div class="card">
                            <header class="card-header">
                                
                                    <p class="card-header-title">Hiawatha 1 Details</p><img class="logo" src="img/hia_logo.png">
                                
                            </header>
                            <div class="card-content">
                                <div class="content">
                                <strong>ID:</strong> <?php echo $h1['id'];?><br>
                                <strong>IP: </strong><span id="hia1IP"></span><br>
                                Using Private Key:<br><?php echo $keyPairName?>
                                
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-one-third">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                Hiawatha 2 Details
                                </p><img class="logo" src="img/hia_logo.png">
                            </header>
                            <div class="card-content">
                                <div class="content">
                                <strong>ID:</strong> <?php echo $h2['id'];?><br>
                                <strong>IP: </strong><span id="hia2IP"></span><br>
                                Using Private Key:<br><?php echo $keyPairName?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-one-third">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                HAProxy Details
                                </p><img class="logo" src="img/haproxy_logo.jpg">
                            </header>
                            <div class="card-content">
                                <div class="content">
                                <strong>ID:</strong> <?php echo $hap['id'];?><br>
                                <strong>IP: </strong><span id="hapIP"></span><br>
                                Using Private Key:<br><?php echo $keyPairName?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="columns">
                    <div class="column is-one-third">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                Gogs Server
                                </p><img class="logo" src="img/gogs_logo.png">
                            </header>
                            <div class="card-content">
                                <div class="content">
                                <strong>ID:</strong> <?php echo $gogs['id'];?><br>
                                <strong>IP: </strong><span id="gogsIP"></span><br>
                                Using Private Key:<br><?php echo $keyPairName?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-one-third">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                OSSEC Server
                                </p><img class="logo" src="img/ossec_logo.png">
                            </header>
                            <div class="card-content">
                                <div class="content">
                                <strong>ID:</strong> <?php echo $ossec['id'];?><br>
                                <strong>IP: </strong><span id="ossecIP"></span><br>
                                Using Private Key:<br><?php echo $keyPairName?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-one-third">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                Vault Details
                                </p><img class="logo" src="img/vault_logo.png">
                            </header>
                            <div class="card-content">
                                <div class="content">
                                <strong>ID:</strong> <?php echo $vault['id'];?><br>
                                <strong>IP: </strong><span id="vaultIP"></span><br>
                                Using Vault Private Key:<br><?php echo $keyPairName2?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>






            <div class="columns">
                    <div class="column is-half">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                Private Key
                                </p>
                            </header>
                            <div class="card-content">
                                <div class="content">
                                    <pre>
                                    <?php
                                        echo $key['KeyMaterial'];
                                    ?>
                                    </pre>
                                </div>
                            </div>
                            <footer class="card-footer">
                                <a class="card-footer-item">Copy</a>
                            </footer>
                            </div>
                    </div>

                    <div class="column is-half">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                Vault Private Key
                                </p>
                            </header>
                            <div class="card-content">
                                <div class="content">
                                    <pre>
                                    <?php
                                        echo $vaultKey['KeyMaterial'];
                                    ?>
                                    </pre>
                                </div>
                            </div>
                            <footer class="card-footer">
                                <a class="card-footer-item">Copy</a>
                            </footer>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                    <div class="column">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                Details
                                </p>
                            </header>
                            <div class="card-content">
                                <div class="content">
                                    <p>
                                    <strong>Gogs User</strong><br>
                                    <strong>U:</strong>admin@cisobox.cloud<br>
                                    <strong>P:</strong>gogsdefault<br>
                                    </p>

                                    <p>
                                    <strong>Gogs Backend Database</strong><br>
                                    <strong>U:</strong>root<br>
                                    <strong>P:</strong>gogsmysql<br>
                                    </p>

                                    <p>
                                    <strong>OSSEC Monitor DB</strong><br>
                                    <strong>U:</strong>monitor<br>
                                    <strong>P:</strong>monitor-cisobox<br>
                                    </p>

                                </div>
                            </div>

                            </div>
                    </div>


        </section>

        <script>
            
                

                setTimeout(function() {
                        document.getElementById("progressBar").value = "55";
                        document.getElementById("currentAction").innerHTML = "Getting server IPs"; 
                        // Load the SDK for JavaScript
                        var creds = new AWS.Credentials({
                            accessKeyId: '<?php echo $keyid?>', secretAccessKey: '<?php echo $secret?>'
                            });
                        AWS.config.credentials = creds;

                        AWS.config.update({region: 'us-east-1'});
                        ec2 = new AWS.EC2({apiVersion: '2016-11-15'});
                        var instanceIDs = [<?php echo "'".$h1['id']."', '".$h2['id']."', '".$hap['id']."', '".$ossec['id']."', '".$gogs['id']."', '".$vault['id']."'";?>];
                        console.log(instanceIDs[0]);

                        var params = {
                            InstanceIds: instanceIDs,
                            DryRun: false
                            };

                        // Call EC2 to retrieve the policy for selected bucket

                        ec2.describeInstances(params, function(err, data) {
                        if (err) {
                            console.log("Error", err.stack);
                        } else {
                            console.log("Success");
                            jdata = JSON.parse(JSON.stringify(data));

                            console.log(jdata);
                            jdata.Reservations.forEach(function(i)
                            {
                                switch(i['Instances'][0]['InstanceId']) {
                                    case instanceIDs[0]:
                                        console.log("found the h1");
                                        document.getElementById("hia1IP").innerHTML = i['Instances'][0]['PublicIpAddress'] + " <a target='_blank' href='http://"+i['Instances'][0]['PublicIpAddress']+"'><i class='fa fa-external-link' aria-hidden='true'></i></a>";
                                        break;
                                    case instanceIDs[1]:
                                        document.getElementById("hia2IP").innerHTML = i['Instances'][0]['PublicIpAddress']+ " <a target='_blank' href='http://"+i['Instances'][0]['PublicIpAddress']+"'><i class='fa fa-external-link' aria-hidden='true'></i></a>";
                                        console.log("found the h2");
                                        break;
                                    case instanceIDs[2]:
                                        document.getElementById("hapIP").innerHTML = i['Instances'][0]['PublicIpAddress']+ " <a target='_blank' href='http://"+i['Instances'][0]['PublicIpAddress']+"'><i class='fa fa-external-link' aria-hidden='true'></i></a>";
                                        console.log("found the haproxy");
                                        break;
                                    case instanceIDs[3]:
                                        document.getElementById("ossecIP").innerHTML = i['Instances'][0]['PublicIpAddress']+ " <a target='_blank' href='http://"+i['Instances'][0]['PublicIpAddress']+"'><i class='fa fa-external-link' aria-hidden='true'></i></a>";
                                        console.log("found the ossec");
                                        break;  
                                    case instanceIDs[4]:
                                        document.getElementById("gogsIP").innerHTML = i['Instances'][0]['PublicIpAddress']+ " <a target='_blank' href='http://"+i['Instances'][0]['PublicIpAddress']+"'><i class='fa fa-external-link' aria-hidden='true'></i></a>";
                                        console.log("found the gogs");
                                        break;   
                                    case instanceIDs[5]:
                                        document.getElementById("vaultIP").innerHTML = i['Instances'][0]['PublicIpAddress']+ " <a target='_blank' href='http://"+i['Instances'][0]['PublicIpAddress']+"'><i class='fa fa-external-link' aria-hidden='true'></i></a>";
                                        console.log("found the vault");
                                        break;  
                                    default:
                                        
                                }
                            });
                        }
                    });
            }, 10000);

            setTimeout(function() { 
                document.getElementById("progressBar").value = "75";
                document.getElementById("currentAction").innerHTML = "Configuring your servers";
                
                setTimeout(function() { 
                document.getElementById("progressBar").value = "100";
                document.getElementById("currentAction").innerHTML = "Done!";
                document.getElementById("spinnyThing").classList.remove('fa-spin');
                }, 45000);
            
           
            }, 15000);


        </script>

        <br>
        <br>
        <br>
        </body>
    </html>


<?php

/*
$h1['id'] = $hia1['Instances'][0]['InstanceId'];
$h2['id'] = $hia2['Instances'][0]['InstanceId'];
$hap['id'] = $hap['Instances'][0]['InstanceId'];
$ossec['id'] = $ossec['Instances'][0]['InstanceId'];
$gogs['id'] = $gogs['Instances'][0]['InstanceId'];
$vault['id'] = $vault['Instances'][0]['InstanceId'];

*/
sleep(65);

$h1['desc'] = $ec2Client->describeInstances(array(
    'InstanceIds' => [$h1['id']],
));

$h2['desc'] = $ec2Client->describeInstances(array(
    'InstanceIds' => [$h2['id']],
));

$hap['desc'] = $ec2Client->describeInstances(array(
    'InstanceIds' => [$hap['id']],
));

$ossec['desc'] = $ec2Client->describeInstances(array(
    'InstanceIds' => [$ossec['id']],
));

$ip = $ossec['desc']['Reservations'][0]['Instances'][0]['PublicIpAddress'];
//echo "IP: " . $ip;
$agentIP1 = $h1['desc']['Reservations'][0]['Instances'][0]['PublicIpAddress'];
$agentIP2 = $h2['desc']['Reservations'][0]['Instances'][0]['PublicIpAddress'];
$haproxyIP = $hap['desc']['Reservations'][0]['Instances'][0]['PublicIpAddress'];

$pk = $key['KeyMaterial'];

$filename = 'input'+$bytes+'.txt';


$inputFile = $ip ." ". $agentIP1 ." ". $agentIP2 ." hap: ". $haproxyIP ." ".$pk." 
";
//echo "<br>";
//echo "inp file: ".$inputFile;

$inputFile .=  "<pre>";
$agentid1 = '007';
$agentid2 = '008';


$top = '<!-- OSSEC example config -->

<ossec_config>
  <client>
    <server-ip>';


$bottom = '</server-ip>
</client>

<syscheck>
  <!-- Frequency that syscheck is executed -- default every 2 hours -->
  <frequency>7200</frequency>

  <!-- Directories to check  (perform all possible verifications) -->
  <directories check_all="yes">/etc,/usr/bin,/usr/sbin</directories>
  <directories check_all="yes">/bin,/sbin</directories>

  <!-- Files/directories to ignore -->
  <ignore>/etc/mtab</ignore>
  <ignore>/etc/hosts.deny</ignore>
  <ignore>/etc/mail/statistics</ignore>
  <ignore>/etc/random-seed</ignore>
  <ignore>/etc/adjtime</ignore>
  <ignore>/etc/httpd/logs</ignore>
</syscheck>

<rootcheck>
  <rootkit_files>/var/ossec/etc/shared/rootkit_files.txt</rootkit_files>
  <rootkit_trojans>/var/ossec/etc/shared/rootkit_trojans.txt</rootkit_trojans>
  <system_audit>/var/ossec/etc/shared/system_audit_rcl.txt</system_audit>
</rootcheck>

<localfile>
  <log_format>syslog</log_format>
  <location>/var/log/syslog</location>
</localfile>

<localfile>
  <log_format>syslog</log_format>
  <location>/var/log/auth.log</location>
</localfile>

<localfile>
  <log_format>syslog</log_format>
  <location>/var/log/dpkg.log</location>
</localfile>

<localfile>
  <log_format>syslog</log_format>
  <location>/var/log/kern.log</location>
</localfile>

<!--

<localfile>
  <log_format>syslog</log_format>
  <location>/var/log/mail.log</location>
</localfile>

<localfile>
  <log_format>apache</log_format>
  <location>/var/log/apache2/access.log</location>
</localfile>

<localfile>
  <log_format>apache</log_format>
  <location>/var/log/apache2/error.log</location>
</localfile>

-->

</ossec_config>
';


$ossec_config = $top.$ip.$bottom;

$inputFile .=  "<br>";

$ssh = new \phpseclib\Net\SSH2($ip);
$rsa = new \phpseclib\Crypt\RSA();
$rsa->loadKey($pk);

sleep(15);

if (!$ssh->login('ubuntu', $rsa)) {
    exit('Login Failed');
}


$command = 'echo -e "a\nhiawatha-1\n'.$agentIP1.'\n'.$agentid1.'\ny\nq\n" | sudo /var/ossec/bin/manage_agents';
$inputFile .=  $ssh->exec($command);
//echo '<hr>';
sleep(5);
$command2 = 'echo -e "a\nhiawatha-2\n'.$agentIP2.'\n'.$agentid2.'\ny\nq\n" | sudo /var/ossec/bin/manage_agents';
$inputFile .=  $ssh->exec($command2);
sleep(5);
$inputFile .=  $ssh->exec('sudo /var/ossec/bin/ossec-control restart');
//echo '<hr>';

sleep(5);


$command3 = 'echo -e "e\n'.$agentid1.'\n\nq\n" | sudo /var/ossec/bin/manage_agents';
$agent_key1 = $ssh->exec($command3);
sleep(3);




//echo "<hr>";
sleep(5);

$command4 = 'echo -e "e\n'.$agentid2.'\n\nq\n" | sudo /var/ossec/bin/manage_agents';
$agent_key2 = $ssh->exec($command4);
sleep(3);

$inputFile .= $agent_key1;
//echo "<hr>";
$inputFile .=  $agent_key2;
//echo "<hr>";

$strStart = strpos($agent_key1,"Agent key information for '007' is: ") + 37;
$step1 = substr($agent_key1,$strStart, 220);
$dubStar = strpos($step1,"** Press ENTER") - 1;
$agent_key1_extracted = substr($step1,0,$dubStar);

$strStart = strpos($agent_key2,"Agent key information for '008' is: ") + 37;
$step1 = substr($agent_key2,$strStart,220);
$dubStar = strpos($step1,"** Press ENTER") - 1;
$agent_key2_extracted = substr($step1,0,$dubStar);




$inputFile .=  "Agent key 1: " . $agent_key1_extracted;

$inputFile .=  "Agent key 2: " . $agent_key2_extracted;



//now config the agents

$config_command = "echo '".$ossec_config."' | sudo tee /var/ossec/etc/ossec.conf";

//$agent_key1_extracted = 'MDA3IGhpYXdhdGhhLTEgNTIuMjAxLjIyNS4yMjcgMTUyNDA3N2E3NTcyNTlhZjk4MjdhMmRiMTY0ZGVlZTQxZjU3MzM0NWZmNDE3YTczZDc2MDlmODRhMTM5ODFmYw==';
//$agent_key2_extracted = 'MDA4IGhpYXdhdGhhLTIgNTQuOTEuMTI5LjE1NCAyYTdmYjZjZjkxYzFlZTEwMjEwZGJkNDA0ZmRjNDZkZWI4ZDdkOTg1N2I5YzcxNjUyZDA2OTAyYWFlM2E0MWMx';

$ssh2 = new \phpseclib\Net\SSH2($agentIP1);
$rsa2 = new \phpseclib\Crypt\RSA();
$rsa2->loadKey($pk);

if (!$ssh2->login('ubuntu', $rsa2)) {
    exit('Login Failed');
}


$command_agent1 = 'echo -e "i\n'.$agent_key1_extracted.'Y\n\nq\n" | sudo /var/ossec/bin/manage_agents';
$inputFile .=  $ssh2->exec($command_agent1);
sleep(2);
$inputFile .=  $ssh2->exec($config_command);
sleep(2);
$inputFile .=  $ssh2->exec('sudo /var/ossec/bin/ossec-control restart');



//agent 2
$ssh3 = new \phpseclib\Net\SSH2($agentIP2);
$rsa3 = new \phpseclib\Crypt\RSA();
$rsa3->loadKey($pk);

if (!$ssh3->login('ubuntu', $rsa3)) {
    exit('Login Failed');
}


$command_agent2 = 'echo -e "i\n'.$agent_key2_extracted.'Y\n\nq\n" | sudo /var/ossec/bin/manage_agents';
$inputFile .=  $ssh3->exec($command_agent2);
sleep(3);
$inputFile .=  $ssh3->exec($config_command);
sleep(2);
$inputFile .=  $ssh3->exec('sudo /var/ossec/bin/ossec-control restart');


$hapTop = "
global
        daemon
        maxconn 256

defaults
        mode tcp
        timeout connect 5000ms
        timeout client 50000ms
        timeout server 50000ms

frontend http-in
        bind *:80
        default_backend servers

backend servers
        balance roundrobin
        server server1 ";
$hapMid = ":80 maxconn 32 check
        server server2 ";
$hapBot = ":80 maxconn 32 check
";
$haproxyCfg = $hapTop . $agentIP1 . $hapMid . $agentIP2 . $hapBot;

$config_command2 = "echo '".$haproxyCfg."' | sudo tee /etc/haproxy/haproxy.cfg";
$haRestart = "sudo service haproxy restart";


$inputFile .= "---------------------------------------------";

//haproxy
$ssh4 = new \phpseclib\Net\SSH2($haproxyIP);
$rsa4 = new \phpseclib\Crypt\RSA();
$rsa4->loadKey($pk);

if (!$ssh4->login('ubuntu', $rsa4)) {
    exit('Login Failed');
}


$inputFile .=  $ssh4->exec($config_command2);
$inputFile .=  "<hr>


";
sleep(3);
$inputFile .=  $ssh4->exec($haRestart);
$inputFile .= "sent ha restart command";
$inputFile .=  "</pre><hr>


";

/*
$handle = fopen($filename, 'w') or die();
fwrite($handle, $inputFile);
*/
?>