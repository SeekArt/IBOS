<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Database']; ?></h1>
		<ul class="mn">
			<li>
				<span><?php echo $lang['Backup']; ?></span>
			</li>
			<li>
				<a href="<?php echo $this->createUrl( 'database/restore' ); ?>"><?php echo $lang['Restore']; ?></a>
			</li>
			<li>
				<a href="<?php echo $this->createUrl( 'database/optimize' ); ?>"><?php echo $lang['Optimize']; ?></a>
			</li>
		</ul>
	</div>
	<div>
		<form action="<?php echo $this->createUrl( 'database/backup' ); ?>" method="post" class="form-horizontal">
			<!-- 数据库备份 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Database backup']; ?></h2>
				<div class="alert trick-tip">
					<div class="trick-tip-title">
						<i></i>
						<strong><?php echo $lang['Skills prompt']; ?></strong>
					</div>
					<div class="trick-tip-content">
						<?php echo $lang['Db tip']; ?>
						<?php echo $lang['Db tip more']; ?>
						<a href="javascript:;" id="tip_more_ctrl"><?php echo $lang['Show all tips']; ?>...</a>
					</div>
				</div>
				<div >
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Backup type']; ?></label>
						<div class="controls" id="backup_type">
							<label class="radio">
								<input type="radio" value="all" name="backuptype" checked>
								<?php echo $lang['IBOS data']; ?>
							</label>
							<label class="radio">
								<input type="radio" value="custom" name="backuptype" >
								<?php echo $lang['Custom backup']; ?>
							</label>
						</div>
					</div>
					<!-- 当选择“自定义备份”时,显示下面列表,默认隐藏 -->
					<!-- a:start -->
					<div class="control-group" id="table_list" style="display:none;">
						<div class="controls">
							<div>
								<p><strong><?php echo $lang['Ibos data table']; ?></strong></p>
								<label class="checkbox">
									<input type="checkbox" data-name="customtables[]">
									<?php echo $lang['Select all']; ?>
								</label>
							</div>
							<div>
								<ul class="database-backup-list clearfix">
									<?php foreach ( $tables as $table ): ?>
										<li>
											<label class="checkbox">
												<input type="checkbox" name="customtables[]" value="<?php echo $table['Name']; ?>"><?php echo $table['Name']; ?>
											</label>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					</div>
					<!-- a:end -->
					<div class="control-group">
						<label class="control-label"><?php echo $lang['More option']; ?></label>
						<div class="controls">
							<input type="checkbox" name="custom_enabled" value="1" id="more_option" data-toggle="switch" class="visi-hidden">
						</div>
					</div>
					<!-- 当“更多选项”开启时，显示下面内容，默认隐藏 -->
					<!-- b:start -->
					<div id="backup_mode" style="display:none;">
						<div class="control-group">
							<label for="" class="control-label"><?php echo $lang['Backup method']; ?></label>
							<div class="controls">
								<label class="radio">
									<input type="radio" name="method" value="shell" />
									<?php echo $lang['Backup method shell']; ?>
								</label>
								<label class="radio" >
									<input type="radio" name="method" value="multivol" checked />
									<?php echo $lang['Backup method all']; ?>
								</label>
							</div>
						</div>
						<!-- 当选择“IBOS分卷备份”时，显示下面内容 -->
						<!-- c:start -->
						<div class="control-group ctbw" id="file_size_limit">
							<label for="" class="control-label"><?php echo $lang['File size limit']; ?></label>
							<div class="controls">
								<div class="input-group">
									<input name="sizelimit" value="2048" type="text">
									<span class="input-group-addon">KB</span>
								</div>
							</div>
						</div>
						<!-- c:end -->
					</div>
				</div>
			</div>
			<!-- 数据备份选项 start -->
			<div class="ctb" id="backup_option" style="display:none;">
				<h2 class="st"><?php echo $lang['Backup option']; ?></h2>
				<div class="ctbw">
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Use Extended insert']; ?></label>
						<div class="controls">
							<label class="radio">
								<input type="radio" name="extendins" value="1" />
								<?php echo $lang['Yes']; ?>
							</label>
							<label class="radio">
								<input type="radio" name="extendins" value="0" checked />
								<?php echo $lang['No']; ?>
							</label>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Sql compat']; ?></label>
						<div class="controls">
							<label class="radio">
								<input type="radio" name="sqlcompat" value="" >
								<?php echo $lang['Default']; ?>
							</label>
							<label class="radio">
								<input type="radio" value="MYSQL40" name="sqlcompat" />
								<?php echo $lang['compat mysql40']; ?>
							</label>
							<label class="radio">
								<input type="radio" name="sqlcompat" checked value="MYSQL41" />
								<?php echo $lang['compat mysql41']; ?>
							</label>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Force charset']; ?></label>
						<div class="controls">
							<label class="radio">
								<input type="radio" name="sqlcharset" value="">
								<?php echo $lang['Default']; ?>
							</label>
							<label class="radio">
								<input type="radio" name="sqlcharset" value="gbk">
								gbk
							</label>
							<label class="radio">
								<input type="radio" name="sqlcharset" checked value="utf8">
								utf-8
							</label>
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Hex']; ?></label>
						<div class="controls">
							<label class="radio">
								<input type="radio" name="usehex" value="1" >
								<?php echo $lang['Yes']; ?>
							</label>
							<label class="radio">
								<input type="radio" name="usehex" checked value="0" />
								<?php echo $lang['No']; ?>
							</label>
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Compressed backup files']; ?></label>
						<div class="controls">
							<label class="radio">
								<input type="radio" name="usezip" value="1" />
								<?php echo $lang['Compressed multi']; ?>
							</label>
							<label class="radio">
								<input type="radio" name="usezip" value="2" />
								<?php echo $lang['Compressed single']; ?>
							</label>
							<label class="radio">
								<input type="radio" name="usezip" value="0" checked />
								<?php echo $lang['No compress']; ?>
							</label>
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Backup filename']; ?></label>
						<div class="controls">
							<div class="input-group">
								<input type="text" name="filename" value="<?php echo $defaultFileName; ?>">
								<span class="input-group-addon">.sql</span>
							</div>
						</div>
					</div>
					<!-- b:end -->
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<button type="submit" value="1" name="dbSubmit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
				</div>
			</div>
		</form>
	</div>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_database.js"></script>