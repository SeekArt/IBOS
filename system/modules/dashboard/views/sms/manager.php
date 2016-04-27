<?php 
use application\core\utils\Convert;
use application\modules\user\model\User;
?>
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/dataTable/css/jquery.dataTables_ibos.min.css?<?php echo VERHASH; ?>">

<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Sms setting']; ?></h1>
		<ul class="mn">
			<!--<li>
				<a href="<?php echo $this->createUrl( 'sms/setup' ); ?>"><?php echo $lang['Sms setup']; ?></a>
			</li>-->
			<li>
				<span><?php echo $lang['Sms sent manager']; ?></span>
			</li>
			<!--<li>
				<a href="<?php echo $this->createUrl( 'sms/access' ); ?>"><?php echo $lang['Sms access']; ?></a>
			</li>-->
		</ul>
	</div>
	<div>
		<form action="javascript:;" method="post" id="sms_search_form">
			<!-- 短信发送管理 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Sms sent manager']; ?></h2>
				<div class="page-list">
					<div class="page-list-header">
						<div class="row">	
							<div class="span9">
								<button type="button" id="exportsms" class="btn"><?php echo $lang['Export']; ?></button>
								<button type="button" id="delsms" class="btn"><?php echo $lang['Delete selected sms']; ?></button>
							</div>
							<div class="span3">
								<div class="search search-config">
									<input type="text" name="keyword" placeholder="Search" id="sms_search">
									<a href="javascript:;">search</a>
								</div>
							</div>
						</div>
					</div>
					<div class="page-list-mainer">
						<table class="table table-striped" id="sms_manage_table">
							<thead>
								<tr>
									<th width="20">
										<label class="checkbox" for="checkbox_0"><span class="icon"></span><span class="icon-to-fade"></span>
											<input type="checkbox" value="" data-name="sms[]" id="checkbox_0">
										</label>
									</th>
									<th width="80"><?php echo $lang['User']; ?></th>
									<th width="80"><?php echo $lang['To user']; ?></th>
									<th><?php echo $lang['Content']; ?></th>
									<th width="120"><?php echo $lang['Result']; ?></th>
									<th width="120"><?php echo $lang['Send time']; ?></th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<div id="d_sms_search" style="display:none;">
	<form action="" id="d_sms_search_form" class="form-horizontal form-narrow">
		<div class="control-group">
			<label for="" class="control-label"><?php echo $lang['Message delivery status']; ?></label>
			<div class="controls">
				<div id="select_type" class="btn-group btn-group-justified" data-toggle="buttons-radio">
					<a href="#" data-id="1" class="btn"><?php echo $lang['Sent succeed'] ?></a>
					<a href="#" data-id="0" class="btn"><?php echo $lang['Sent failure'] ?></a>
					<a href="#" data-id="" class="btn"><?php echo $lang['All of it']; ?></a>
				</div>
				<input type="hidden" name="searchtype" id="type" />
			</div>
		</div>
		<div class="control-group">
			<label for="" class="control-label"><?php echo $lang['Sent time scope']; ?></label>
			<div class="controls">
				<div class="row">
					<div class="span6">
						<div class="datepicker" id="date_start">
							<a href="javascript:;" class="datepicker-btn"></a>
							<input type="text" class="datepicker-input" name="begin">
						</div>
					</div>
					<div class="span6">
						<div class="datepicker" id="date_end">
							<a href="javascript:;" class="datepicker-btn"></a>
							<input type="text" class="datepicker-input" name="end">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang['Sender']; ?></label>
			<div class="controls">
				<input type="text" name="sender" id="sender" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang['Recipient phone number']; ?></label>
			<div class="controls">
				<input type="text" name="recnumber">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang['Content']; ?></label>
			<div class="controls">
				<textarea name="content" rows="3"></textarea>
			</div>
		</div>
	</form>
</div>
<script src="<?php echo STATICURL; ?>/js/lib/dataTable/js/jquery.dataTables.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/db_sms.js"></script>
