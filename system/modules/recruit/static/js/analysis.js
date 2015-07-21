/**
 * analysis.js
 * 简历一建分析JS
 * IBOS
 * @author		gzhzh
 * @version		$Id: recruit.js 1899 2013-12-12 12:44:21Z gzhzh $
 * @modified	2014-01-07 
 */
(function(){
	var op = Ibos.app.g("RSM_ADD_OP"),
		impInfo = Ibos.app.g("RSM_ADD_IMPINFO");
	if (op == 'analysis') { // 如果是导入简历，进行各项赋值
			var impInfoObj = $.parseJSON(impInfo);
		/**
		 * 基本信息
		 */
		if (typeof impInfoObj.name !== 'undefined') { // 姓名
			$("input[name='realname']").val(impInfoObj.name);
		}
		if (typeof impInfoObj.gender !== 'undefined') { // 性别
			$("input[name='gender'][value='" + impInfoObj.gender + "']").attr("checked", "checked");
		}
		if (typeof impInfoObj.birthday !== 'undefined') { // 生日
			$("input[name='birthday']").val(impInfoObj.birthday);
		}
		if (typeof impInfoObj.birthplace !== 'undefined') { // 籍贯
			$("input[name='birthplace']").val(impInfoObj.birthplace);
		}
		if (typeof impInfoObj.workyears !== 'undefined' && impInfoObj.workyears != '') { // 工作年限
			var aOption = $("[name='workyears']").find("option");
			for (var j = 0; j < aOption.length; j++) {
				if (aOption[j].value == impInfoObj.workyears) {
					var index = j;
					break;
				}
			}
			if (index >= 0) {
				aOption[index].selected = true;
			}
		}
		if (typeof impInfoObj.education !== 'undefined') { // 学历
			var bOption = $("[name='education']").find("option");
			for (var j = 0; j < bOption.length; j++) {
				if (bOption[j].text == impInfoObj.education) {
					var index = j;
					break;
				}
			}
			if (index >= 0) {
				bOption[index].selected = true;
			}
		}
		if (typeof impInfoObj.height !== 'undefined') { // 身高
			$("input[name='height']").val(impInfoObj.height);
		}
		if (typeof impInfoObj.weight !== 'undefined') { // 体重
			$("input[name='weight']").val(impInfoObj.weight);
		}
		if (typeof impInfoObj.idcard !== 'undefined') { // 身份证
			$("input[name='idcard']").val(impInfoObj.idcard);
		}
		if (typeof impInfoObj.maritalstatus !== 'undefined') { // 婚姻状态
			$("input[name='maritalstatus'][value='" + impInfoObj.maritalstatus + "']").attr("checked", "checked");
		}
		/**
		 * 联系方式
		 */
		if (typeof impInfoObj.residecity !== 'undefined') { // 居住地址
			$("input[name='residecity']").val(impInfoObj.residecity);
		}
		if (typeof impInfoObj.zipcode !== 'undefined') { // 邮编
			$("input[name='zipcode']").val(impInfoObj.zipcode);
		}
		if (typeof impInfoObj.mobile !== 'undefined') { // 手机
			$("input[name='mobile']").val(impInfoObj.mobile);
		}
		if (typeof impInfoObj.email !== 'undefined') { // Email
			$("input[name='email']").val(impInfoObj.email);
		}
		if (typeof impInfoObj.phone !== 'undefined') { // 电话
			$("input[name='telephone']").val(impInfoObj.phone);
		}
		if (typeof impInfoObj.qq !== 'undefined') { // QQ
			$("input[name='qq']").val(impInfoObj.qq);
		}
		if (typeof impInfoObj.msn !== 'undefined') { // MSN
			$("input[name='msn']").val(impInfoObj.msn);
		}
		/**
		 * 求职意向
		 */
		if (typeof impInfoObj.beginworkday !== 'undefined') { // 到岗时间
			$("input[name='beginworkday']").val(impInfoObj.beginworkday);
		}
		if (typeof impInfoObj.expectsalary !== 'undefined') { // 期望月薪
			$("input[name='expectsalary']").val(impInfoObj.expectsalary);
		}
		if (typeof impInfoObj.workplace !== 'undefined') { // 工作地点
			$("input[name='workplace']").val(impInfoObj.workplace);
		}
		/**
		 * 详细信息
		 */
		if (typeof impInfoObj.workexperience !== 'undefined') { // 工作经验
			$("[name='workexperience']").html(impInfoObj.workexperience);
		}
		if (typeof impInfoObj.projectexperience !== 'undefined') { // 项目经验
			$("[name='projectexperience']").html(impInfoObj.projectexperience);
		}
		if (typeof impInfoObj.eduexperience !== 'undefined') { // 教育背景
			$("[name='eduexperience']").html(impInfoObj.eduexperience);
		}
		if (typeof impInfoObj.languageskill !== 'undefined') { // 语言能力
			$("[name='langskill']").html(impInfoObj.languageskill);
		}
		if (typeof impInfoObj.computerskill !== 'undefined') { // IT技能
			$("[name='computerskill']").html(impInfoObj.computerskill);
		}
		if (typeof impInfoObj.relatedskill !== 'undefined') { // 职业技能
			$("[name='professionskill']").html(impInfoObj.relatedskill);
		}
		if (typeof impInfoObj.trainexperience !== 'undefined') { // 培训经历
			$("[name='trainexperience']").html(impInfoObj.trainexperience);
		}
		if (typeof impInfoObj.evaluation !== 'undefined') { // 自我评价
			$("[name='selfevaluation']").html(impInfoObj.evaluation);
		}
		if (typeof impInfoObj.relevantcertificates !== 'undefined') { // 相关证书
			$("[name='relevantcertificates']").html(impInfoObj.relevantcertificates);
		}
		if (typeof impInfoObj.socialpractice !== 'undefined') { // 社会实践
			$("[name='socialpractice']").html(impInfoObj.socialpractice);
		}
	}
})();

