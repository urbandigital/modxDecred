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
$pool4publicurl = 'http://www.zpool.ca/api/status';
$pool5publicurl = 'https://www2.coinmine.pl/dcr/index.php?page=api&action=public';
$pool6publicurl = 'http://yiimp.ccminer.org/api/status';

/* mining pool PRIVATE apiCredentials */
$pool1_apikey = $modx->getOption('decred.pool1_apikey',$scriptProperties,'YOURAPIKEY');
$pool1_userid = $modx->getOption('decred.pool1_userid',$scriptProperties,'YOURUSERID');

$pool2_apikey = $modx->getOption('decred.pool2_apikey',$scriptProperties,'YOURAPIKEY');
$pool2_userid = $modx->getOption('decred.pool2_userid',$scriptProperties,'YOURUSERID');
$output = '';

if($job == "getbalancespendable"){
$getbalance = $decredRpcWallet->getbalance('default',0,'spendable');
}

if($job == "getbalancelocked"){
$getbalance = $decredRpcWallet->getbalance('default',0,'locked');
echo $getbalance;
}

if($job == "getbalanceall"){
$getbalance = $decredRpcWallet->getbalance('default',0,'all');
echo $getbalance;
}

if($job == "getbalancespendable"){
$getbalance = $decredRpcWallet->getbalance('default',0,'spendable');
echo $getbalance;
}

if($job == "getticketmaxprice"){
$getticketmaxprice = $decredRpcWallet->getticketmaxprice();
echo $getticketmaxprice;
}

if($job == "getdifficulty"){
$tpl = $modx->getOption('tpl',$scriptProperties,'tplGetstakeinfo');
$values = array('getdifficulty' => intval($decredRpcDaemon->getdifficulty()));
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "getbestblock"){
$tpl = $modx->getOption('tpl',$scriptProperties,'landingpageGetbestblock');
$jsonbestblock = $decredRpcDaemon->getbestblock();
$values = array('getbestblock' => $jsonbestblock['height']);
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "getgenerate"){
$getgenerate = $decredRpcDaemon->getgenerate();
    if($getgenerate==true){
        echo "yes";
    }else{
        echo "no";
    }
}

if($job == "getstakedifficulty"){
$tpl = $modx->getOption('tpl',$scriptProperties,'tplGetstakeinfo');
$getstakedifficulty = $decredRpcDaemon->getstakedifficulty();
$values = array('getstakedifficulty' => round($decredRpcDaemon->getstakedifficulty(),3));
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "getnetworkhashps"){
$tpl = $modx->getOption('tpl',$scriptProperties,'landingpageGetnetworkhashps');
$getnetworkhashps = $decredRpcDaemon->getnetworkhashps();
$getnetworkhashps = $getnetworkhashps / 1000000000000;
$getnetworkhashps = substr ($getnetworkhashps , 0, 6);
$values = array('getnetworkhashps' => $getnetworkhashps );
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "getblockcount"){
$getblockcount = $decredRpcDaemon->getblockcount();
echo $getblockcount;
}


if($job == "missedtickets"){
$missedtickets = $decredRpcDaemon->missedtickets();
$flu = json_decode($missedtickets, true);
echo $flu;
}

if($job == "getstakeinfo"){
$tpl = $modx->getOption('tpl',$scriptProperties,'tplGetstakeinfo');
$jsongetstakeinfo = $decredRpcWallet->getstakeinfo();
$profit = $jsongetstakeinfo['live'] + $jsongetstakeinfo['immature'];
$profit = $profit * 1.87174959;
$proportionlive = $jsongetstakeinfo['poolsize'] / 100;
$proportionlive = $jsongetstakeinfo['live'] / $proportionlive;
$proportionlive = round($proportionlive, 2);
$values = array('poolsize' => $jsongetstakeinfo['poolsize'],
                'difficulty' => $jsongetstakeinfo['difficulty'],
                'allmempooltix' => $jsongetstakeinfo['allmempooltix'],
                'ownmempooltix' => $jsongetstakeinfo['ownmempooltix'],
                'immature' => $jsongetstakeinfo['immature'],
                'live' => $jsongetstakeinfo['live'],
                'proportionlive' => $proportionlive,
                'voted' => $jsongetstakeinfo['voted'],
                'totalsubsidy' => $jsongetstakeinfo['totalsubsidy'],
                'missed' => $jsongetstakeinfo['missed'],
                'proportionmissed' => $jsongetstakeinfo['proportionmissed'],
                'revoked' => $jsongetstakeinfo['revoked'],
                'profit' => $profit);

$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}


if($job == "mempoolfee"){
$rawmempool = $decredRpcDaemon->getrawmempool(true);
$sum = 0;
$manytix = 0;
foreach($rawmempool as $ticket){
    if($ticket['fee']>"0"){
    $sum = $sum + $ticket['fee'];
    $manytix = $manytix +1 ;
    }
}

// $manytix = count($rawmempool);
$averagefee = $sum / $manytix;

$values = array('mempoolfee' => round($averagefee, 5));
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "avgblocktime"){
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
$c->limit(100);
$blocktimes = $modx->getCollection('Blocks',$c);

$earlier = 0;
$i = 1;
foreach($blocktimes as $block) {
            $later = $block->get('blocktime');
            if($i!=1){
               $differenceInSeconds = date("U",$earlier) - date("U",$later);
               $allSeconds = $allSeconds + $differenceInSeconds;
               
            }
                $i = $i + 1;
               $earlier = $later;
}
$avgtime = $allSeconds / 100;
$avgtime = gmdate("H:i:s", $avgtime);
$values = array('avgblocktime' => $avgtime);
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "getrawmempool"){
$rawmempool = $decredRpcDaemon->getrawmempool(true);

$values = array('allmempooltix' => count($rawmempool));
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "decredprice"){
$content=file_get_contents($exchangeBceEur);
$data=json_decode($content, true);
$lastdealeur=$data['btc_eur']['last'];
$content2=file_get_contents($exchangeBittrex);
$data2=json_decode($content2, true);
$lastdealbtclast=$data2['result']['0']['Last'];
$lastdealbtchigh=$data2['result']['0']['High'];
$lastdealbtclow=$data2['result']['0']['Low'];
$europricelast=$lastdealeur*$lastdealbtclast;
$europricelow=$lastdealeur*$lastdealbtclow;
$europricehigh=$lastdealeur*$lastdealbtchigh;
$values = array('lastdealprice' => round($europricelast, 2),
                'lastdealpricelow' => round($europricelow, 2),
                'lastdealpricehigh' => round($europricehigh, 2),
                'lasttrade' => $lastdealbtclast);
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "lastpriceeur"){
$content=file_get_contents($exchangeBceEur);
$data=json_decode($content, true);
$lastdealeur=$data['btc_eur']['last'];
$content2=file_get_contents($exchangeBittrex);
$data2=json_decode($content2, true);
$lastdealbtc=$data2['result']['0']['Last'];
$europrice=$lastdealeur*$lastdealbtc;
$values = array('lastpriceeur' => round($europrice, 2));
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "lowpriceeur"){
$content=file_get_contents($exchangeBceEur);
$data=json_decode($content, true);
$lastdealeur=$data['btc_eur']['last'];
$content2=file_get_contents($exchangeBittrex);
$data2=json_decode($content2, true);
$lastdealbtc=$data2['result']['0']['Low'];
$europrice=$lastdealeur*$lastdealbtc;
$values = array('lowpriceeur' => round($europrice, 2));
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "highpriceeur"){
$content=file_get_contents($exchangeBceEur);
$data=json_decode($content, true);
$lastdealeur=$data['btc_eur']['last'];
$content2=file_get_contents($exchangeBittrex);
$data2=json_decode($content2, true);
$lastdealbtc=$data2['result']['0']['High'];
$europrice=$lastdealeur*$lastdealbtc;
$values = array('highpriceeur' => round($europrice, 2));
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "lasttrade"){
$content2=file_get_contents($exchangeBittrex);
$data2=json_decode($content2, true);
$lasttrade=$data2['result']['0']['Last'];
$values = array('lasttrade' => $lasttrade);
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

if($job == "allmytickets"){
$jsongetstakeinfo = $decredRpcWallet->getstakeinfo();
echo $jsongetstakeinfo['live'] + $jsongetstakeinfo['immature'];
}

// POOL 1
if($job =="poolSuprnovapublic"){
    if(!$content=file_get_contents($pool2publicurl)) {
   $tpl=`landingpagePoolerror`;
}
$data=json_decode($content, true);
$hashrate = $data['hashrate'] / 1000000;
$hashrate = substr($hashrate, 0,6);
$foundblockhash = $decredRpcDaemon->getblockhash($data['last_block']);
$foundblock = $decredRpcDaemon->getblock($foundblockhash);
$foundtime = date('g:i a',$foundblock['time']);
$timeago = $dcr->timeAgo($foundblock['time']);
$network_hashrate = $data['network_hashrate'] / 1000000000;
// GET MINED BLOCKS
$path = MODX_CORE_PATH . 'components/decred/';
$result = $modx->addPackage('decred',$path .
    'model/','modx_decred_');

if (! $result) {
    return 'no database connection - come back later';
} else {
    $output = '<p>added Package</p>';
}
$pool = "1";
$minedblocks = $modx->getCollection('Blocks', array('pool' => $pool));
$minedblocks = count($minedblocks);
// CALCULATE POOL LUCK
$today = strtotime('today midnight');
$tomorrow = $today + (1 * 24 * 60 * 60);
 $c = $modx->newQuery('Blocks');
    $c->where(array(
    'blocktime:>='=>$today,
    'blocktime:<'=>$tomorrow,
    'pool'=>$pool,
    ));
    $latestdbblocks = '';
    $latestdbblocks = $modx->getCollection('Blocks',$c);

    $foundblocks = count($latestdbblocks);
    $luck = $foundblocks / $hashrate * 1000;
    $luck = round($luck,1);
$values = array('hashrate' => $hashrate,
                'luck' => $luck,
                'workers' => $data['workers'],
                'shares_this_round' => $data['shares_this_round'],
                'foundblockhash' => $foundblockhash,
                'lastblock' => $data['last_block'],
                'foundtime' => $foundtime,
                'timeago' => $timeago,
                'network_hashrate' => $network_hashrate,
                'countminedblocks' => $minedblocks,
                'block_confirmations' => $foundblock['confirmations'],
                'block_size' => $foundblock['size'],
                'block_height' => $foundblock['height'],
                'block_merkleroot' => $foundblock['merkleroot'],
                'block_stakeroot' => $foundblock['stakeroot'],
                'block_time' => $foundblock['time'],
                'block_nonce' => $foundblock['nonce'],
                'block_votebits' => $foundblock['votebits'],
                'block_finalstate' => $foundblock['finalstate'],
                'block_voters' => $foundblock['voters'],
                'block_freshstake' => $foundblock['freshstake'],
                'block_revocations' => $foundblock['revocations'],
                'block_poolsize' => $foundblock['poolsize'],
                'block_bits' => $foundblock['bits'],
                'block_sbits' => $foundblock['sbits'],
                'block_difficulty' => $foundblock['difficulty'],
                'block_previousblockhash' => $foundblock['previousblockhash'],
                'block_nextblockhash' => $foundblock['nextblockhash']);
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

// POOL 2
if($job =="poolMaxminerspublic"){
    if(!$content=file_get_contents($pool3publicurl)) {
   $tpl=`landingpagePoolerror`;
}
$data=json_decode($content, true);
$hashrate = $data['hashrate'] / 1000000;
$hashrate = substr($hashrate, 0,6);
$foundblockhash = $decredRpcDaemon->getblockhash($data['last_block']);
$foundblock = $decredRpcDaemon->getblock($foundblockhash);
$foundtime = date('g:i a',$foundblock['time']);
$timeago = $dcr->timeAgo($foundblock['time']);
$network_hashrate = $data['network_hashrate'] / 1000000000;
// GET MINED BLOCKS
$path = MODX_CORE_PATH . 'components/decred/';
$result = $modx->addPackage('decred',$path .
    'model/','modx_decred_');

if (! $result) {
    return 'no database connection - come back later';
} else {
    $output = '<p>added Package</p>';
}
$pool = "2";
$minedblocks = $modx->getCollection('Blocks', array('pool' => $pool));
$minedblocks = count($minedblocks);
// CALCULATE POOL LUCK
$today = strtotime('today midnight');
$tomorrow = $today + (1 * 24 * 60 * 60);
 $c = $modx->newQuery('Blocks');
    $c->where(array(
    'blocktime:>='=>$today,
    'blocktime:<'=>$tomorrow,
    'pool'=>$pool,
    ));
    $latestdbblocks = '';
    $latestdbblocks = $modx->getCollection('Blocks',$c);

    $foundblocks = count($latestdbblocks);
    $luck = $foundblocks / $hashrate * 1000;
    $luck = round($luck,1);
$values = array('hashrate' => $hashrate,
                'luck' => $luck,
                'workers' => $data['workers'],
                'shares_this_round' => $data['shares_this_round'],
                'foundblockhash' => $foundblockhash,
                'lastblock' => $data['last_block'],
                'foundtime' => $foundtime,
                'timeago' => $timeago,
                'network_hashrate' => $network_hashrate,
                'countminedblocks' => $minedblocks,
                'block_confirmations' => $foundblock['confirmations'],
                'block_size' => $foundblock['size'],
                'block_height' => $foundblock['height'],
                'block_merkleroot' => $foundblock['merkleroot'],
                'block_stakeroot' => $foundblock['stakeroot'],
                'block_time' => $foundblock['time'],
                'block_nonce' => $foundblock['nonce'],
                'block_votebits' => $foundblock['votebits'],
                'block_finalstate' => $foundblock['finalstate'],
                'block_voters' => $foundblock['voters'],
                'block_freshstake' => $foundblock['freshstake'],
                'block_revocations' => $foundblock['revocations'],
                'block_poolsize' => $foundblock['poolsize'],
                'block_bits' => $foundblock['bits'],
                'block_sbits' => $foundblock['sbits'],
                'block_difficulty' => $foundblock['difficulty'],
                'block_previousblockhash' => $foundblock['previousblockhash'],
                'block_nextblockhash' => $foundblock['nextblockhash']);
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

// POOL 3
if($job =="poolPoolmnpublic"){
    if(!$content=file_get_contents($pool1publicurl)) {
   $tpl=`landingpagePoolerror`;
}
$data=json_decode($content, true);
$hashrate = $data['hashrate'] / 1000000;
$hashrate = substr($hashrate, 0,6);
$foundblockhash = $decredRpcDaemon->getblockhash($data['last_block']);
$foundblock = $decredRpcDaemon->getblock($foundblockhash);
$foundtime = date('g:i a',$foundblock['time']);
$timeago = $dcr->timeAgo($foundblock['time']);
$network_hashrate = $data['network_hashrate'] / 1000000000;
// GET MINED BLOCKS
$path = MODX_CORE_PATH . 'components/decred/';
$result = $modx->addPackage('decred',$path .
    'model/','modx_decred_');

if (! $result) {
    return 'no database connection - come back later';
} else {
    $output = '<p>added Package</p>';
}
$pool = "3";
$minedblocks = $modx->getCollection('Blocks', array('pool' => $pool));
$minedblocks = count($minedblocks);
// CALCULATE POOL LUCK
$today = strtotime('today midnight');
$tomorrow = $today + (1 * 24 * 60 * 60);
 $c = $modx->newQuery('Blocks');
    $c->where(array(
    'blocktime:>='=>$today,
    'blocktime:<'=>$tomorrow,
    'pool'=>$pool,
    ));
    $latestdbblocks = '';
    $latestdbblocks = $modx->getCollection('Blocks',$c);

    $foundblocks = count($latestdbblocks);
    $luck = $foundblocks / $hashrate * 1000;
    $luck = round($luck,1);
$values = array('hashrate' => $hashrate,
                'luck' => $luck,
                'workers' => $data['workers'],
                'shares_this_round' => $data['shares_this_round'],
                'foundblockhash' => $foundblockhash,
                'lastblock' => $data['last_block'],
                'foundtime' => $foundtime,
                'timeago' => $timeago,
                'network_hashrate' => $network_hashrate,
                'countminedblocks' => $minedblocks,
                'block_confirmations' => $foundblock['confirmations'],
                'block_size' => $foundblock['size'],
                'block_height' => $foundblock['height'],
                'block_merkleroot' => $foundblock['merkleroot'],
                'block_stakeroot' => $foundblock['stakeroot'],
                'block_time' => $foundblock['time'],
                'block_nonce' => $foundblock['nonce'],
                'block_votebits' => $foundblock['votebits'],
                'block_finalstate' => $foundblock['finalstate'],
                'block_voters' => $foundblock['voters'],
                'block_freshstake' => $foundblock['freshstake'],
                'block_revocations' => $foundblock['revocations'],
                'block_poolsize' => $foundblock['poolsize'],
                'block_bits' => $foundblock['bits'],
                'block_sbits' => $foundblock['sbits'],
                'block_difficulty' => $foundblock['difficulty'],
                'block_previousblockhash' => $foundblock['previousblockhash'],
                'block_nextblockhash' => $foundblock['nextblockhash']);
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

// POOL 4
if($job =="poolZpoolpublic"){
    if(!$content=file_get_contents($pool4publicurl)) {
   $tpl=`landingpagePoolerror`;
}
$data=json_decode($content, true);
$hashrate = $data['decred']['hashrate'] / 1000000000;
$hashrate = substr($hashrate, 0,6);
$workers = $data['decred']['workers'];
$lastblock = $data['decred']['lastbloc'];
$foundblockhash = $decredRpcDaemon->getblockhash($data['decred']['lastbloc']);
$foundblock = $decredRpcDaemon->getblock($foundblockhash);
$foundtime = date('g:i a',$foundblock['time']);
$timeago = $dcr->timeAgo($foundblock['time']);


// GET MINED BLOCKS
$path = MODX_CORE_PATH . 'components/decred/';
$result = $modx->addPackage('decred',$path .
    'model/','modx_decred_');
if (! $result) {
    return 'no database connection - come back later';
} else {
    $output = '<p>added Package</p>';
}
$pool = "4";
$minedblocks = $modx->getCollection('Blocks', array('pool' => $pool));
$minedblocks = count($minedblocks);

// CALCULATE POOL LUCK
$today = strtotime('today midnight');
$tomorrow = $today + (1 * 24 * 60 * 60);
 $c = $modx->newQuery('Blocks');
    $c->where(array(
    'blocktime:>='=>$today,
    'blocktime:<'=>$tomorrow,
    'pool'=>$pool,
    ));
    $latestdbblocks = '';
    $latestdbblocks = $modx->getCollection('Blocks',$c);

    $foundblocks = count($latestdbblocks);
    $luck = $foundblocks / $hashrate * 1000;
    $luck = round($luck,1);


$values = array('hashrate' => $hashrate,
                'luck' => $luck,
                'workers' => $workers,
                'lastblock' => $lastblock,
                'foundtime' => $foundtime,
                'timeago' => $timeago,
                'network_hashrate' => $network_hashrate,
                'countminedblocks' => $minedblocks,
                'block_confirmations' => $foundblock['confirmations'],
                'block_size' => $foundblock['size'],
                'block_height' => $foundblock['height'],
                'block_merkleroot' => $foundblock['merkleroot'],
                'block_stakeroot' => $foundblock['stakeroot'],
                'block_time' => $foundblock['time'],
                'block_nonce' => $foundblock['nonce'],
                'block_votebits' => $foundblock['votebits'],
                'block_finalstate' => $foundblock['finalstate'],
                'block_voters' => $foundblock['voters'],
                'block_freshstake' => $foundblock['freshstake'],
                'block_revocations' => $foundblock['revocations'],
                'block_poolsize' => $foundblock['poolsize'],
                'block_bits' => $foundblock['bits'],
                'block_sbits' => $foundblock['sbits'],
                'block_difficulty' => $foundblock['difficulty'],
                'block_previousblockhash' => $foundblock['previousblockhash'],
                'block_nextblockhash' => $foundblock['nextblockhash']);
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

// POOL 5
if($job =="poolCoinmineplpublic"){
    if(!$content=file_get_contents($pool5publicurl)) {
   $tpl=`landingpagePoolerror`;
}
$data=json_decode($content, true);
$hashrate = $data['hashrate'] / 1000000;
$hashrate = substr($hashrate, 0,6);
$foundblockhash = $decredRpcDaemon->getblockhash($data['last_block']);
$foundblock = $decredRpcDaemon->getblock($foundblockhash);
$foundtime = date('g:i a',$foundblock['time']);
$timeago = $dcr->timeAgo($foundblock['time']);
$network_hashrate = $data['network_hashrate'] / 1000000000;
// GET MINED BLOCKS
$path = MODX_CORE_PATH . 'components/decred/';
$result = $modx->addPackage('decred',$path .
    'model/','modx_decred_');

if (! $result) {
    return 'no database connection - come back later';
} else {
    $output = '<p>added Package</p>';
}
$pool = "5";
$minedblocks = $modx->getCollection('Blocks', array('pool' => $pool));
$minedblocks = count($minedblocks);
// CALCULATE POOL LUCK
$today = strtotime('today midnight');
$tomorrow = $today + (1 * 24 * 60 * 60);
 $c = $modx->newQuery('Blocks');
    $c->where(array(
    'blocktime:>='=>$today,
    'blocktime:<'=>$tomorrow,
    'pool'=>$pool,
    ));
    $latestdbblocks = '';
    $latestdbblocks = $modx->getCollection('Blocks',$c);

    $foundblocks = count($latestdbblocks);
    $luck = $foundblocks / $hashrate * 1000;
    $luck = round($luck,1);
$values = array('hashrate' => $hashrate,
                'luck' => $luck,
                'workers' => $data['workers'],
                'shares_this_round' => $data['shares_this_round'],
                'foundblockhash' => $foundblockhash,
                'lastblock' => $data['last_block'],
                'foundtime' => $foundtime,
                'timeago' => $timeago,
                'network_hashrate' => $network_hashrate,
                'countminedblocks' => $minedblocks,
                'block_confirmations' => $foundblock['confirmations'],
                'block_size' => $foundblock['size'],
                'block_height' => $foundblock['height'],
                'block_merkleroot' => $foundblock['merkleroot'],
                'block_stakeroot' => $foundblock['stakeroot'],
                'block_time' => $foundblock['time'],
                'block_nonce' => $foundblock['nonce'],
                'block_votebits' => $foundblock['votebits'],
                'block_finalstate' => $foundblock['finalstate'],
                'block_voters' => $foundblock['voters'],
                'block_freshstake' => $foundblock['freshstake'],
                'block_revocations' => $foundblock['revocations'],
                'block_poolsize' => $foundblock['poolsize'],
                'block_bits' => $foundblock['bits'],
                'block_sbits' => $foundblock['sbits'],
                'block_difficulty' => $foundblock['difficulty'],
                'block_previousblockhash' => $foundblock['previousblockhash'],
                'block_nextblockhash' => $foundblock['nextblockhash']);
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}

// POOL 6
if($job =="poolYjimppublic"){
if(!$content=file_get_contents($pool6publicurl)) {
   $tpl=`landingpagePoolerror`;
}
$data=json_decode($content, true);
$hashrate = $data['decred']['hashrate'] / 1000000000;
$hashrate = substr($hashrate, 0,6);
$workers = $data['decred']['workers'];
$lastblock = $data['decred']['lastbloc'];
$foundblockhash = $decredRpcDaemon->getblockhash($data['decred']['lastbloc']);
$foundblock = $decredRpcDaemon->getblock($foundblockhash);
$foundtime = date('g:i a',$foundblock['time']);
$timeago = $dcr->timeAgo($foundblock['time']);

// GET MINED BLOCKS
$path = MODX_CORE_PATH . 'components/decred/';
$result = $modx->addPackage('decred',$path .
    'model/','modx_decred_');
if (! $result) {
    return 'no database connection - come back later';
} else {
    $output = '<p>added Package</p>';
}
$pool = "6";
$minedblocks = $modx->getCollection('Blocks', array('pool' => $pool));
$minedblocks = count($minedblocks);
// CALCULATE POOL LUCK
$today = strtotime('today midnight');
$tomorrow = $today + (1 * 24 * 60 * 60);
 $c = $modx->newQuery('Blocks');
    $c->where(array(
    'blocktime:>='=>$today,
    'blocktime:<'=>$tomorrow,
    'pool'=>$pool,
    ));
    $latestdbblocks = '';
    $latestdbblocks = $modx->getCollection('Blocks',$c);

    $foundblocks = count($latestdbblocks);
    $luck = $foundblocks / $hashrate * 1000;
    $luck = round($luck,1);
$values = array('hashrate' => $hashrate,
                'luck' => $luck,
                'workers' => $workers,
                'lastblock' => $lastblock,
                'foundtime' => $foundtime,
                'timeago' => $timeago,
                'network_hashrate' => $network_hashrate,
                'countminedblocks' => $minedblocks,
                'block_confirmations' => $foundblock['confirmations'],
                'block_size' => $foundblock['size'],
                'block_height' => $foundblock['height'],
                'block_merkleroot' => $foundblock['merkleroot'],
                'block_stakeroot' => $foundblock['stakeroot'],
                'block_time' => $foundblock['time'],
                'block_nonce' => $foundblock['nonce'],
                'block_votebits' => $foundblock['votebits'],
                'block_finalstate' => $foundblock['finalstate'],
                'block_voters' => $foundblock['voters'],
                'block_freshstake' => $foundblock['freshstake'],
                'block_revocations' => $foundblock['revocations'],
                'block_poolsize' => $foundblock['poolsize'],
                'block_bits' => $foundblock['bits'],
                'block_sbits' => $foundblock['sbits'],
                'block_difficulty' => $foundblock['difficulty'],
                'block_previousblockhash' => $foundblock['previousblockhash'],
                'block_nextblockhash' => $foundblock['nextblockhash']);
$output = $modx->parseChunk($tpl, $values, '[+', '+]');
return $output;
}


if($job =="poolPoolprivateuserdata"){

    $jobname = 'getuserbalance';
    $poolPoolmnprivateurl ='https://pool.mn/dcr/index.php?page=api&action='.$jobname.'&api_key='.$pool1_apikey.'&id='.$pool1_userid;
    $content=file_get_contents($poolPoolmnprivateurl);
    $data=json_decode($content, true);
    echo 'Confirmed: '.$data['getuserbalance']['data']['confirmed'].'<br/>';
    echo 'Unconfirmed: '.$data['getuserbalance']['data']['unconfirmed'].'<br/>';

    $jobname = 'getuserhashrate';
    $poolPoolmnprivateurl ='https://pool.mn/dcr/index.php?page=api&action='.$jobname.'&api_key='.$pool1_apikey.'&id='.$pool1_userid;

    $content=file_get_contents($poolPoolmnprivateurl);
    $data=json_decode($content, true);
    $userhashrate = $data['getuserhashrate']['data'] / 1000;
  echo 'Userhashrate: '.substr($userhashrate, 0, 6).' MH/s<br/>';

}

if($job =="poolSuprnovaprivateuserdata"){
    $jobname = 'getuserbalance';
    $poolPoolmnprivateurl ='https://dcr.suprnova.cc/index.php?page=api&action='.$jobname.'&api_key='.$pool2_apikey.'&id='.$pool2_userid;
    $content=file_get_contents($poolPoolmnprivateurl);
    $data=json_decode($content, true);
    echo 'Confirmed: '.$data['getuserbalance']['data']['confirmed'].'<br/>';
    echo 'Unconfirmed: '.$data['getuserbalance']['data']['unconfirmed'].'<br/>';

    $jobname = 'getuserhashrate';
    $poolPoolmnprivateurl ='https://dcr.suprnova.cc/index.php?page=api&action='.$jobname.'&api_key='.$pool2_apikey.'&id='.$pool2_userid;

    $content=file_get_contents($poolPoolmnprivateurl);
    $data=json_decode($content, true);
    $userhashrate = $data['getuserhashrate']['data'] / 1000;
    echo 'Userhashrate: '.substr($userhashrate, 0, 6).' MH/s<br/>';
}

if($job == "votesperday"){
    $commandjob = shell_exec('cat dcrwallet.log  | grep Voted | grep -o "[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]" | uniq -c');
    echo '<pre>'.$commandjob.'</pre>';
}

if($job == "ticketsperday"){
    $commandjob = shell_exec('cat dcrwallet.log | grep SStx | grep -o "[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]" | uniq -c');
    echo '<pre>'.$commandjob.'</pre>';
}












return $output;