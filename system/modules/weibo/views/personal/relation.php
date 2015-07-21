<?php use application\core\utils\String; ?>
<?php if ( !empty( $list ) ): ?>
	<ul class="list-inline wb-ava-list" data-node-type="relationList">
		<?php foreach ( $list as $user ): ?>
			<li>
				<a data-toggle="usercard" data-param="uid=<?php echo $user['uid']; ?>" href="<?php echo $user['space_url']; ?>" title="<?php echo $user['realname']; ?>">
					<span class="avatar-circle">
						<img src="<?php echo $user['avatar_middle']; ?>" alt="<?php echo $user['realname']; ?>" />
					</span>
					<p><?php echo String::cutStr( $user['realname'], 10 ) ?></p>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>