<?php
$dcr = $modx->getService('decred','Decred',$modx->getOption('decred.core_path',null,$modx->getOption('core_path').'components/dcr/').'model/dcr/',$scriptProperties);

if (!($dcr instanceof Decred)) return '';

/* setup default properties */
$tpl = $modx->getOption('tpl',$scriptProperties,'rowTpl');
$sort = $modx->getOption('sort',$scriptProperties,'name');
$dir = $modx->getOption('dir',$scriptProperties,'ASC');

/* dcrd credentials */
$dcrDaemonUsername = $modx->getOption('decred.daemon_username',$scriptProperties,'username');
$dcrDaemonPass = $modx->getOption('decred.daemon_pass',$scriptProperties,'password');
$dcrDaemonIp = $modx->getOption('decred.daemon_ip',$scriptProperties,'127.0.0.1');
$dcrDaemonPort = $modx->getOption('decred.daemon_port',$scriptProperties,'9109');

/* dcrwallet credentials */
$dcrWalletUsername =  $modx->getOption('decred.wallet_username',$scriptProperties,'username');
$dcrWalletPass = $modx->getOption('decred.wallet_pass',$scriptProperties,'password');
$dcrWalletIp = $modx->getOption('decred.wallet_ip',$scriptProperties,'127.0.0.1');
$dcrWalletPort = $modx->getOption('decred.wallet_port',$scriptProperties,'9110');

/* construction of the jsonRPC URL */
$daemonCall = 'http://'.$dcrDaemonUsername.':'.$dcrDaemonPass.'@'.$dcrDaemonIp.':'.$dcrDaemonPort.'/';
$walletCall = 'http://'.$dcrWalletUsername.':'.$dcrWalletPass.'@'.$dcrWalletIp.':'.$dcrWalletPort.'/';

/* initializing the jsonRPCClient library class */
include_once $modx->getOption('decred.core_path').'jsonrpcphp/includes/jsonRPCClient.php';
$decredRpcDaemon = new jsonRPCClient($daemonCall);
$decredRpcWallet = new jsonRPCClient($walletCall);

/* 3rd Party data sources (exchange rates/datapoints) */
$exchangeBittrex = 'https://bittrex.com/api/v1.1/public/getmarketsummary?market=btc-dcr';
$exchangeBceEur = 'https://btc-e.com/api/3/ticker/btc_eur';
$exchangeCoinmarketCap = 'https://api.coinmarketcap.com/v1/datapoints/decred/';

/* mining pool PUBLIC apiURIs */
$pool1publicurl = 'https://pool.mn/dcr/index.php?page=api&action=public';
$pool2publicurl = 'https://dcr.suprnova.cc/index.php?page=api&action=public';
$pool3publicurl = 'https://dcr.maxminers.net/index.php?page=api&action=public';

/* mining pool PRIVATE apiCredentials */
$pool1_apikey = $modx->getOption('decred.pool1_apikey',$scriptProperties,'YOURAPIKEY');
$pool1_userid = $modx->getOption('decred.pool1_userid',$scriptProperties,'YOURUSERID');

$pool2_apikey = $modx->getOption('decred.pool2_apikey',$scriptProperties,'YOURAPIKEY');
$pool2_userid = $modx->getOption('decred.pool2_userid',$scriptProperties,'YOURUSERID');
$output = '';


// GET ACTUAL BLOCK HEIGHT
$jsonbestblock = $decredRpcDaemon->getbestblock();
$to = $jsonbestblock['height'];

// PREPARE DATABASE ACCESS
$path = MODX_CORE_PATH . 'components/decred/';
$result = $modx->addPackage('decred',$path .
    'model/','modx_decred_');

if (! $result) {
    return 'no database connection - come back later';
} else {
    $output = '';
}

$c = $modx->newQuery('Blocks');
$c->sortby('id','DESC');
$c->limit(1);
$latestdbblocks = $modx->getCollection('Blocks',$c);

foreach($latestdbblocks as $latestblock) {
            $height = $latestblock->get('blocknr');
}
if($height<$to){
$height = $height + 1;


// ITTERATE TROUGH EACH BLOCK AND COLLECT DATA
 for( $i= $height ; $i <= $to ; $i++ )
{

$blocknr = $i;
$blockhash = $decredRpcDaemon->getblockhash($i);
$blockinfos = $decredRpcDaemon->getblock($blockhash);
// CHECK TRANSACTIONS IN BLOCK FOR MINING POOL REWARDS/ADDRESSES

$workreward = $blockinfos['tx']['0'];
$stakereward = $blockinfos['stx']['0'];

$workreward = $decredRpcDaemon->getrawtransaction($workreward);
$workreward = $decredRpcDaemon->decoderawtransaction($workreward);

$rewarddev = $workreward['vout']['0']['value'];
$rewarddevaddress = $workreward['vout']['0']['scriptPubKey']['addresses']['0'];
$rewardwork = $workreward['vout']['2']['value'];
$rewardworkaddress = $workreward['vout']['2']['scriptPubKey']['addresses']['0'];

    $miningpool="0";
            if ($rewardworkaddress=="DsYAN3vT15rjzgoGgEEscoUpPCRtwQKL7dQ"){
                $miningpool="1"; // suprnova.cc
            }
            if ($rewardworkaddress=="Dshg5YEfSo6MKX5wC5DkWhQMWAoLFtvXiWH"){
                $miningpool="2"; // maxminers.net
            }
            if ($rewardworkaddress=="DshZYJySTD4epCyoKRjPMyVmSvBpFuNYuZ4"){
                $miningpool="3"; // pool.mn
            }
            if ($rewardworkaddress=="DsUgKCK8AMcdRCH5p5Hcaeyw77PPXu3sDsV"){
                $miningpool="4"; // zpool.ca
            }
            if ($rewardworkaddress=="DsZWrNNyKDUFPNMcjNYD7A8k9a4HCM5xgsW"){
                $miningpool="5"; // coinmine.pl
            }
            if ($rewardworkaddress=="Dsg2bQy2yt2onEcaQhT1X9UbTKNtqmHyMus"){
                $miningpool="6"; // yiimp.ccminer.org
            }
            if ($rewardworkaddress=="DsVECaj9tP9jb2PvsStMgmNYCYxt1NS7ZwY"){
                $miningpool="7"; // Mr.X
            }


$timestamper = date("Y-m-d H:i:s", $blockinfos['time']);
$timestamper = $timestamper;
// $output .= "New Block: ".$blocknr.' Pool: '.$miningpool.'<br />';
// PUT BLOCK DATA INTO DATABASE

$fields = array(
    'blocknr' => $i,
    'blocktime' => $blockinfos['time'],
    'pool' => $miningpool,
    'voters' => $blockinfos['voters'],
    'freshstake' => $blockinfos['freshstake'],
    'poolsize' => $blockinfos['poolsize'],
    'nethash' => '0',
    'rewarddev' => $rewarddev,
    'rewarddevaddress' => $rewarddevaddress,
    'rewardwork' => $rewardwork,
    'rewardworkaddress' => $rewardworkaddress,
);
$quote = $modx->newObject('Blocks', $fields);
$quote->save();

}
}
return $output;
