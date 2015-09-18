var Ibos = Ibos||{};
	(function(window, Ibos){
		Ibos.pushValue = function(value, source){
			var result;
			if(source.length){
				result = source + "," +value;
			}else{
				result = value;
			}
			return result;
		}
		Ibos.popValue = function(value, source, global){
			global = (global == false) ? false : true;
			var arr = source.split(",");
			for(var i = 0; i < arr.length; i++){
				if(value === arr[i]){
					arr.splice(i, 1);
					if(!global){
						break;
					}
				}
			}
			return arr.join(",")
		}
		Ibos.checkExists = function(value, source){
			return (source.indexOf(value) > -1);
		}
	})(window, Ibos);
	
	(function(){
		var createItem = function(content){
			var item = $("<li>"),
				trashBtn;
			trashBtn = $("<a>", {
				"href": "javascript:;",
				"class": "cbtn o-trash",
				"title": "删除"
			})
			item.text(content).append(trashBtn).data("value", content);
			return item;
		}
		var addItem = function(value, list, input){
			var source = input.val();
			var item;
			if(!Ibos.checkExists(value, source)){
				item = createItem(value);
				list.append(item);
				input.val(Ibos.pushValue(value, source));
			}else{
				alert("该项已存在列表中");
			}
		}
		var removeItem = function(item, input){
			var value = item.data("value"),
				source = input.val();
			input.val(Ibos.popValue(value, source));
			item.remove();
		}

		var mapBtn = $("#map_btn"),
			mapProp = $("#map_prop"),
			mapList = $("#map_list"),
			mapField = $("#map_field"),
			mapValue = $("#map_value");


		var addMapItem = function(){
			var field = mapField.val(),
				prop = mapProp.val(),
				item, value, source = mapValue.val();
			if(field && prop){
				value = field + "=>" +prop;
				addItem(value, mapList, mapValue)
			}
		};


		mapField.on("dblclick", "option", addMapItem);
		mapProp.on("dblclick", "option", addMapItem);
		mapBtn.on("click", addMapItem);
		mapList.on("click", "a", function(evt){
			var item = $(evt.target.parentNode);
			removeItem(item, mapValue);
		})

		var condProposer = $("#cond_proposer"),
			condType = $("#cond_type"),
			condStaff = $("#cond_staff"),
			condBtn = $("#cond_btn"),
			condList = $("#cond_list"),
			condValue = $("#cond_value");

		var addCondItem = function(){
			var proposer = condProposer.val(),
				type = condType.val(),
				staff = condStaff.val(),
				value, source = condValue.val();
			if(staff){
				value = proposer + type + staff;
				addItem(value, condList, condValue);
			}
		};

		condBtn.on("click", addCondItem);
		condList.on("click", "a", function(evt){
			var item = $(evt.target.parentNode);
			removeItem(item, condValue);
		})
	})();