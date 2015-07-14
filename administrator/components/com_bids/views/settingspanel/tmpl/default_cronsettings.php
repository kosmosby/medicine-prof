<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action = "index.php?option=com_rbids" method = "post" name = "adminForm" id = "adminForm">
	<input type = "hidden" name = "task" value = "">

	<div style = "background-color: #FFFFFF;padding: 10px;">
		<h1>Setting up CRON for Auctions Factory</h1>

		<h3>Why do you need a Cron Job?</h3>

		<p style = "font-size: 110%;">
			Cron jobs are tasks that are run at specific time from your hosting provider. These tasks ensure that specific auction task run at specific times. This way you make sure that
			for
			instance
			your users get the notifications about upcoming expiring auctions or information regarding their watchlists.
		</p>

		<h3>Where do i set up my Cron Jobs?</h3>

		<p style = "font-size: 110%;">
			This depends on your hosting. Please <a href = "http://www.thefactory.ro/support/documentation/the-factory---a-simple-cron-tutorial..html"> check our documentation page for
			some
			examples</a>
		</p>

		<h3>What tasks do i set up in my Cron Jobs?</h3>

		<p style = "font-size: 110%;">
			There are two tasks that must run - a common task that may run every 5-15 minutes, and a daily task that runs once a day.<br />
			The common task closes current expiring auctions and notifies winners/auctioneers. <br />
			Our reccomandation is to set up the common at a 5 minutes pace and the daily at midnight.<br />
			The links you must set up to be accessed in cron are:
		</p>
		<ul style = "font-size: 130%;">
			<li>Common task:<strong><?php echo JUri::root();?>index.php?option=<?php echo APP_EXTENSION;?>&controller=crontask&pass=<?php echo $this->cfg->bid_opt_cron_password;?></strong></li>
			<li>Daily task: <strong><?php echo JUri::root();?>index.php?option=<?php echo APP_EXTENSION;?>&controller=crontask&daily=1&pass=<?php echo $this->cfg->bid_opt_cron_password;?></strong>
			</li>
		</ul>
		<p>You can set up / change the password for the Cron jobs in <a href = "index.php?option=<?php echo APP_EXTENSION;?>&task=config.display">General Settings , first tab - Global settings
			area</a>.
		</p>

		<h2>The last time a Cron Job run on Auction Factory: <?php echo ($this->cronlog) ? ($this->cronlog->logtime) : (JText::_("COM_BIDS_NEVER"));?>  </h2>

		<h3>More info and resources</h3>
		<ul>
			<li><a href = "http://www.thefactory.ro/documentation/cron-job-settings-for-the-factory-extensions.html">The Factory Documentation</a></li>
			<li><a href = "http://www.siteground.com/tutorials/cpanel/cron_jobs.htm">Cron jobs in CPANEL Hostings</a></li>
			<li><a href = "http://help.godaddy.com/article/3547">Cron Jobs in Godaddy Hostings</a></li>
			<li><a href = "http://wiki.dreamhost.com/Cron_Jobs">Cron jobs in Dreamhost panel</a></li>
		</ul>
	</div>
</form>
