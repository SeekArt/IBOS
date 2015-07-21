<link rel="stylesheet" type="text/css" href="<?php echo $assetUrl; ?>/css/contact_table.css">
<div class="main-content">
	<div class="contect-list-info">
		<span class="table-title"><?php echo isset( $unit['fullname'] ) ? $unit['fullname'] : ''; ?><?php echo $lang['Contact']; ?></span>
	</div>
	<table class="info-table">
		<thead>
			<tr>
				<th><?php echo $lang['Department']; ?></th>
				<th><?php echo $lang['Name']; ?></th>
				<th><?php echo $lang['Position']; ?></th>
				<th><?php echo $lang['Phone']; ?></th>
				<th><?php echo $lang['Cell phone']; ?></th>
				<th><?php echo $lang['Email']; ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( count( $datas ) > 0 ): ?>
				<?php foreach ( $datas as $k => $dept ): ?>
					<?php $index = 0; ?>
					<?php foreach ( $dept['users'] as $uid => $user ): ?>
						<tr>
							<?php if ( $index == 0 ): ?>
								<td rowspan="<?php echo count( $dept['users'] ); ?>"><?php echo $dept['deptname']; ?></td>
							<?php endif; ?>
							<td><?php echo $user['realname'] ?></td>
							<td><?php echo $user['posname']; ?></td>
							<td><?php echo $user['telephone']; ?></td>
							<td><?php echo $user['mobile']; ?></td>
							<td><?php echo $user['email']; ?></td>
						</tr>
						<?php $index++; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>	
