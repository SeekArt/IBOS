
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Recruitment management']; ?></h1>
	</div>
	<form action="<?php echo $this->createUrl( 'dashboard/update' ) ?>" method="post">
		<!-- 招聘管理 start -->
		<div class="ctb">
			<h2 class="st"><?php echo $lang['Resume field management']; ?></h2>
			<table class="table table-bordered table-hover table-operate" style="width:70%">
				<thead>
					<tr>
						<th><?php echo $lang['Field name']; ?></th>
						<th><?php echo $lang['Default state']; ?> ( <?php echo $lang['Fold']; ?>/<?php echo $lang['Unfold']; ?> )</th>
						<th><?php echo $lang['Validation rules']; ?></th>
					</tr>
				</thead>
				<tbody>
					<tr style="background-color: #f9fafa;">
						<td colspan="3" style="background-color: #f9fafa;">
							<strong><?php echo $lang['Basic information']; ?></strong>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Full name']; ?></td>
						<td>
							<input type="checkbox" name="recruitrealname[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitrealname']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitrealname[fieldrule]">
								<option value="<?php echo $config['recruitrealname']['regulartype'] ?>"><?php echo $config['recruitrealname']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitrealname']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Sex']; ?></td>
						<td>
							<input type="checkbox" name="recruitsex[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitsex']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitsex[fieldrule]">
								<option value="<?php echo $config['recruitsex']['regulartype'] ?>"><?php echo $config['recruitsex']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitsex']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Date of birth']; ?></td>
						<td>
							<input type="checkbox" name="recruitbirthday[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitbirthday']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitbirthday[fieldrule]">
								<option value="<?php echo $config['recruitbirthday']['regulartype'] ?>"><?php echo $config['recruitbirthday']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitbirthday']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Hometown']; ?></td>
						<td>
							<input type="checkbox" name="recruitbirthplace[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitbirthplace']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitbirthplace[fieldrule]">
								<option value="<?php echo $config['recruitbirthplace']['regulartype'] ?>"><?php echo $config['recruitbirthplace']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitbirthplace']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Work years']; ?></td>
						<td>
							<input type="checkbox" name="recruitworkyears[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitworkyears']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitworkyears[fieldrule]">
								<option value="<?php echo $config['recruitworkyears']['regulartype'] ?>"><?php echo $config['recruitworkyears']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitworkyears']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Record of formal schooling']; ?></td>
						<td>
							<input type="checkbox" name="recruiteducation[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruiteducation']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruiteducation[fieldrule]">
								<option value="<?php echo $config['recruiteducation']['regulartype'] ?>"><?php echo $config['recruiteducation']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruiteducation']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['CV status']; ?></td>
						<td>
							<input type="checkbox" name="recruitstatus[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitstatus']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitstatus[fieldrule]">
								<option value="<?php echo $config['recruitstatus']['regulartype'] ?>"><?php echo $config['recruitstatus']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitstatus']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Idcard']; ?></td>
						<td>
							<input type="checkbox" name="recruitidcard[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitidcard']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitidcard[fieldrule]">
								<option value="<?php echo $config['recruitidcard']['regulartype'] ?>"><?php echo $config['recruitidcard']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitidcard']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Height']; ?></td>
						<td>
							<input type="checkbox" name="recruitheight[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitheight']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitheight[fieldrule]">
								<option value="<?php echo $config['recruitheight']['regulartype'] ?>"><?php echo $config['recruitheight']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitheight']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Body weight']; ?></td>
						<td>
							<input type="checkbox" name="recruitweight[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitweight']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitweight[fieldrule]">
								<option value="<?php echo $config['recruitweight']['regulartype'] ?>"><?php echo $config['recruitweight']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitweight']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Marital status']; ?></td>
						<td>
							<input type="checkbox" name="recruitmaritalstatus[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitmaritalstatus']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitmaritalstatus[fieldrule]">
								<option value="<?php echo $config['recruitmaritalstatus']['regulartype'] ?>"><?php echo $config['recruitmaritalstatus']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitmaritalstatus']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3" style="background-color: #f9fafa;">
							<strong><?php echo $lang['Contact mode']; ?></strong>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Residence address']; ?></td>
						<td>
							<input type="checkbox" name="recruitresidecity[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitresidecity']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitresidecity[fieldrule]">
								<option value="<?php echo $config['recruitresidecity']['regulartype'] ?>"><?php echo $config['recruitresidecity']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitresidecity']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Zipcode']; ?></td>
						<td>
							<input type="checkbox" name="recruitzipcode[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitzipcode']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitzipcode[fieldrule]">
								<option value="<?php echo $config['recruitzipcode']['regulartype'] ?>"><?php echo $config['recruitzipcode']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitzipcode']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Phone']; ?></td>
						<td>
							<input type="checkbox" name="recruitmobile[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitmobile']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitmobile[fieldrule]">
								<option value="<?php echo $config['recruitmobile']['regulartype'] ?>"><?php echo $config['recruitmobile']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitmobile']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Email']; ?></td>
						<td>
							<input type="checkbox" name="rucruitemail[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['rucruitemail']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="rucruitemail[fieldrule]">
								<option value="<?php echo $config['rucruitemail']['regulartype'] ?>"><?php echo $config['rucruitemail']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['rucruitemail']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Telephone']; ?></td>
						<td>
							<input type="checkbox" name="recruittelephone[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruittelephone']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruittelephone[fieldrule]">
								<option value="<?php echo $config['recruittelephone']['regulartype'] ?>"><?php echo $config['recruittelephone']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruittelephone']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['QQ']; ?></td>
						<td>
							<input type="checkbox" name="recruitqq[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitqq']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitqq[fieldrule]">
								<option value="<?php echo $config['recruitqq']['regulartype'] ?>"><?php echo $config['recruitqq']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitqq']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['MSN']; ?></td>
						<td>
							<input type="checkbox" name="recruitmsn[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitmsn']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitmsn[fieldrule]">
								<option value="<?php echo $config['recruitmsn']['regulartype'] ?>"><?php echo $config['recruitmsn']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitmsn']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3" style="background-color: #f9fafa;">
							<strong><?php echo $lang['Job target']; ?></strong>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Work time']; ?></td>
						<td>
							<input type="checkbox" name="recruitbeginworkday[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitbeginworkday']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitbeginworkday[fieldrule]">
								<option value="<?php echo $config['recruitbeginworkday']['regulartype'] ?>"><?php echo $config['recruitbeginworkday']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitbeginworkday']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Job candidates']; ?></td>
						<td>
							<input type="checkbox" name="recruittargetposition[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruittargetposition']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruittargetposition[fieldrule]">
								<option value="<?php echo $config['recruittargetposition']['regulartype'] ?>"><?php echo $config['recruittargetposition']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruittargetposition']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Salary expectations']; ?></td>
						<td>
							<input type="checkbox" name="recruitexpectsalary[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitexpectsalary']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitexpectsalary[fieldrule]">
								<option value="<?php echo $config['recruitexpectsalary']['regulartype'] ?>"><?php echo $config['recruitexpectsalary']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitexpectsalary']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Workplace']; ?></td>
						<td>
							<input type="checkbox" name="recruitworkplace[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitexpectsalary']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitworkplace[fieldrule]">
								<option value="<?php echo $config['recruitworkplace']['regulartype'] ?>"><?php echo $config['recruitworkplace']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitworkplace']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3" style="background-color: #f9fafa;">
							<strong><?php echo $lang['Details']; ?></strong>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Resume source']; ?></td>
						<td>
							<input type="checkbox" name="recruitrecchannel[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitrecchannel']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitrecchannel[fieldrule]">
								<option value="<?php echo $config['recruitrecchannel']['regulartype'] ?>"><?php echo $config['recruitrecchannel']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitrecchannel']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Work Experience']; ?></td>
						<td>
							<input type="checkbox" name="recruitworkexperience[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitworkexperience']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitworkexperience[fieldrule]">
								<option value="<?php echo $config['recruitworkexperience']['regulartype'] ?>"><?php echo $config['recruitworkexperience']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitworkexperience']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Project Experience']; ?></td>
						<td>
							<input type="checkbox" name="recruitprojectexperience[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitprojectexperience']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitprojectexperience[fieldrule]">
								<option value="<?php echo $config['recruitprojectexperience']['regulartype'] ?>"><?php echo $config['recruitprojectexperience']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitprojectexperience']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Educational background']; ?></td>
						<td>
							<input type="checkbox" name="recruiteduexperience[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruiteduexperience']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruiteduexperience[fieldrule]">
								<option value="<?php echo $config['recruiteduexperience']['regulartype'] ?>"><?php echo $config['recruiteduexperience']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruiteduexperience']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Language skills']; ?></td>
						<td>
							<input type="checkbox" name="recruitlangskill[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitlangskill']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitlangskill[fieldrule]">
								<option value="<?php echo $config['recruitlangskill']['regulartype'] ?>"><?php echo $config['recruitlangskill']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitlangskill']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['IT skills']; ?></td>
						<td>
							<input type="checkbox" name="recruitcomputerskill[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitcomputerskill']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitcomputerskill[fieldrule]">
								<option value="<?php echo $config['recruitcomputerskill']['regulartype'] ?>"><?php echo $config['recruitcomputerskill']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitcomputerskill']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Vocational skills']; ?></td>
						<td>
							<input type="checkbox" name="recruitprofessionskill[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitprofessionskill']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitprofessionskill[fieldrule]">
								<option value="<?php echo $config['recruitprofessionskill']['regulartype'] ?>"><?php echo $config['recruitprofessionskill']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitprofessionskill']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Training experience']; ?></td>
						<td>
							<input type="checkbox" name="recruittrainexperience[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruittrainexperience']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruittrainexperience[fieldrule]">
								<option value="<?php echo $config['recruittrainexperience']['regulartype'] ?>"><?php echo $config['recruittrainexperience']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruittrainexperience']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Self-evaluation']; ?></td>
						<td>
							<input type="checkbox" name="recruitselfevaluation[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitselfevaluation']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitselfevaluation[fieldrule]">
								<option value="<?php echo $config['recruitselfevaluation']['regulartype'] ?>"><?php echo $config['recruitselfevaluation']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitselfevaluation']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Relevant certificate']; ?></td>
						<td>
							<input type="checkbox" name="recruitrelevantcertificates[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitrelevantcertificates']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitrelevantcertificates[fieldrule]">
								<option value="<?php echo $config['recruitrelevantcertificates']['regulartype'] ?>"><?php echo $config['recruitrelevantcertificates']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitrelevantcertificates']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $lang['Social practice']; ?></td>
						<td>
							<input type="checkbox" name="recruitsocialpractice[visi]" id="" data-toggle="switch" class="visi-hidden" value="1" <?php if ( $config['recruitsocialpractice']['visi'] ): ?>checked<?php endif; ?>>
						</td>
						<td>
							<select name="recruitsocialpractice[fieldrule]">
								<option value="<?php echo $config['recruitsocialpractice']['regulartype'] ?>"><?php echo $config['recruitsocialpractice']['regulardesc'] ?></option>
								<?php foreach ( $config['regular'] as $key => $value ): ?>
									<?php if ( $value['type'] != $config['recruitsocialpractice']['regulartype'] ): ?>
										<option value="<?php echo $value['type'] ?>"><?php echo $value['desc'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
								<option value="regex" class="regex"><?php echo $lang['Regular expressions']; ?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<button type="submit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Save']; ?></button>
		</div>
	</form>
</div>
<script>
	$(function() {
		$('select').change(function() {
			if ($(this).val() === 'regex') {
				var selectName = $(this).attr('name');
				$(this).parent().html('<input type="text" name="' + selectName + '"/>');
			}
		});
	});
</script>