<?php
global $wtwconnect;
try {
	/* google analytics tracking (if defined in wtw_config.php) */
	$wtwconnect->trackPageView($wtwconnect->domainurl."/connect/wtw-3dinternet-getavatar.php");
	
	/* get values from querystring or session */
	$zinstanceid = base64_decode($wtwconnect->getVal('i',''));
	$zuserid = base64_decode($wtwconnect->getVal('d',''));
	$zcommunityid = base64_decode($wtwconnect->getVal('c',''));
	$zbuildingid = base64_decode($wtwconnect->getVal('b',''));
	
	$zfounduseravatarid = "";
	$zanonuseravatarid = "";
	$zuseruseravatarid = "";
	
	/* select useravatarid data */
	$zresults = $wtwconnect->query("
		select useravatarid 
		from ".WTW_3DINTERNET_PREFIX."useravatars 
		where instanceid='".$zinstanceid."' 
			and userid='' 
			and deleted=0 
		order by updatedate desc limit 1;");
	foreach ($zresults as $zrow) {
		$zanonuseravatarid = $zrow["useravatarid"];
	}

	$zresults = $wtwconnect->query("
		select useravatarid 
		from ".WTW_3DINTERNET_PREFIX."useravatars 
		where instanceid='".$zinstanceid."' 
			and userid='".$zuserid."' 
			and (not userid='') 
			and deleted=0 
		order by updatedate desc limit 1;");
	foreach ($zresults as $zrow) {
		$zuseruseravatarid = $zrow["useravatarid"];
	}
	if (!empty($zuserid) && isset($zuserid)) {
		if (!empty($zuseruseravatarid) && isset($zuseruseravatarid)) {
			$zfounduseravatarid = $zuseruseravatarid;
		} else {
			$zfounduseravatarid = $zanonuseravatarid;
		}
	} else {
		$zfounduseravatarid = $zanonuseravatarid;
	}
	
	echo $wtwconnect->addConnectHeader($wtwconnect->domainname);

	$zavatardef = array();
	$zavatar = array();
	$zavatarparts = array();
	$zuseravatarid = "";
	$zavatarind = 0;
	$zobject = array();
	$zdisplayname = "Anonymous";
	$zprivacy = 0;
	$zenteranimation = "1";
	$zenteranimationparameter = "";
	$zexitanimation = "1";
	$zexitanimationparameter = "";
	$zscaling = array();
	$zgraphics = array(
		'waterreflection'=>'1',
		'receiveshadows'=>'0'
	);
	$zavataranimationdefs = array();
	if (!empty($zfounduseravatarid) && isset($zfounduseravatarid)) {
		$zresults = $wtwconnect->query("
			select a.*,
				case when '".$zuserid."' = '' then 'Anonymous'
					when not a.userid='".$zuserid."' then 'Anonymous' 
					else
						a.userid
				end as currentdisplayname
			from ".WTW_3DINTERNET_PREFIX."useravatars a
			where a.useravatarid='".$zfounduseravatarid."'
				and a.deleted=0;");

	
		$i = 0;
		foreach ($zresults as $zrow) {
			$zpersoninstanceid = $zrow["instanceid"];
			$zuseravatarid = $zrow["useravatarid"];
			$zavatarind = $zrow["avatarind"];
			$zdisplayname = $zrow["displayname"];
			$zprivacy = $zrow["privacy"];
			$zenteranimation = $zrow["enteranimation"];
			$zenteranimationparameter = $zrow["enteranimationparameter"];
			$zexitanimation = $zrow["exitanimation"];
			$zexitanimationparameter = $zrow["exitanimationparameter"];
			$zscaling = array(
				'x'=> $zrow["scalingx"], 
				'y'=> $zrow["scalingy"], 
				'z'=> $zrow["scalingz"]
			);
			$zobject = array(
				'uploadobjectid'=>'',
				'folder'=> $zrow["objectfolder"],
				'file'=> $zrow["objectfile"],
				'walkspeed'=>$zrow["walkspeed"],
				'walkanimationspeed'=>$zrow["walkanimationspeed"],
				'turnspeed'=>$zrow["turnspeed"],
				'turnanimationspeed'=>$zrow["turnanimationspeed"],
				'objectanimations'=>null
			);
			
			$zresults2 = $wtwconnect->query("
				select c.*
				from ".WTW_3DINTERNET_PREFIX."useravatarcolors c 
				where c.useravatarid='".$zfounduseravatarid."'
					and c.deleted=0
				order by c.avatarpart, c.avatarpartid;");
			$j = 0;
			foreach ($zresults2 as $zrow2) {
				$zavatarparts[$j] = array(
					'avatarpartid'=> $zrow2["avatarpartid"],
					'avatarpart'=> $zrow2["avatarpart"],
					'emissivecolorr'=> $zrow2["emissivecolorr"],
					'emissivecolorg'=> $zrow2["emissivecolorg"],
					'emissivecolorb'=> $zrow2["emissivecolorb"]
				); 
				$j += 1;
			}
			
			$zresults2 = $wtwconnect->query("
				select u.*
				from ".WTW_3DINTERNET_PREFIX."useravataranimations u 
				where u.useravatarid='".$zfounduseravatarid."'
					and u.deleted=0
				order by u.loadpriority desc, u.avataranimationname, u.avataranimationid, u.useravataranimationid;");
			$j = 0;
			foreach ($zresults2 as $zrow2) {
				$zavataranimationdefs[$j] = array(
					'animationind'=> $j,
					'useravataranimationid'=> $zrow2["useravataranimationid"],
					'avataranimationid'=> $zrow2["avataranimationid"],
					'animationname'=> $zrow2["avataranimationname"],
					'animationfriendlyname'=> $zrow2["animationfriendlyname"],
					'loadpriority'=> $zrow2["loadpriority"],
					'animationicon'=> $zrow2["animationicon"],
					'speedratio'=> $zrow2["speedratio"],
					'defaultspeedratio'=> $zrow2["speedratio"],
					'objectfolder'=> $zrow2["objectfolder"],
					'objectfile'=> $zrow2["objectfile"],
					'startframe'=> $zrow2["startframe"],
					'endframe'=> $zrow2["endframe"],
					'animationloop'=> $zrow2["animationloop"],
					'walkspeed'=> $zrow2["walkspeed"]
				);
				$j += 1;
			}

			$zavatardef = array(
				'name'=> "person-".$zpersoninstanceid, 
				'useravatarid'=> $zuseravatarid, 
				'avatarind'=> $zavatarind,
				'position'=>array(
					'x'=>'0',
					'y'=>'0',
					'z'=>'0'),
				'scaling'=> $zscaling,
				'rotation'=>array(
					'x'=>'0',
					'y'=>'0',
					'z'=>'0'),
				'object'=> $zobject,
				'instanceid'=> $zpersoninstanceid,
				'userid'=> $zuserid, 
				'displayname'=> $zdisplayname, 
				'opacity'=>'1',
				'graphics'=> $zgraphics,
				'checkcollisions'=>'0',
				'ispickable'=>'1',
				'parentname'=>'',
				'moveevents'=> '',
				'privacy'=> $zprivacy,
				'enteranimation'=> $zenteranimation,
				'enteranimationparameter'=> $zenteranimationparameter,
				'exitanimation'=> $zexitanimation,
				'exitanimationparameter'=> $zexitanimationparameter,
				'avatarparts'=> $zavatarparts,
				'avataranimationdefs'=> $zavataranimationdefs,
				'animations'=> array(),
				'updated'=> '0',
				'loaded'=> '0');
			$i += 1;	
		}
	}
	echo json_encode($zavatardef);	
} catch (Exception $e) {
	$wtwconnect->serror("connect-wtw-3dinternet-getavatar.php=".$e->getMessage());
}
?>
