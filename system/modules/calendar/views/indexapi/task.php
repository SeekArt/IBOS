<link rel="stylesheet" href="<?php echo $assetUrl . '/css/calendar.css' ?>">
<style>
    .in-todo-table {
    }

    .in-todo-table tr td {
        height: 39px;
        color: #58585C;
    }

    .in-todo-table .in-todo-complete td {
        text-decoration: line-through;
        color: #82939E;
    }

    .in-todo-table tr:hover .o-todo-uncomplete {
        visibility: visible;
    }
</style>

<table class="table in-todo-table" id="calendar_task">
    <tbody></tbody>
</table>

<script>
    (function () {
        var template = '<tr class="bdbs <% if(complete == 1){ %>in-todo-complete<% } %>">' +
                '<td width="20">' +
                '<a href="javascript:;" class="o-todo-<% if(complete == 1){ %>complete<% }else{ %>uncomplete<% } %>" data-id="<%= id %>"></a>' +
                '</td>' +
                '<td><%= text %></td>' +
                '</tr>',
            task = <?php echo json_encode($taskList); ?>,
            html = "";
        if (task && task.length) {
            for (var i = 0; i < task.length; i++) {
                html += $.template(template, task[i]).replace(/&amp;/g, '&');
            }
            $("#calendar_task tbody").html(html);
        }
        function complete(id, isComplete) {
            var url = Ibos.app.url("calendar/task/edit", {op: "complete"});
            return $.post(url, {
                id: id,
                complete: +isComplete,
                formhash: Ibos.app.g("formHash")
            });
        }

        function toggleRowState($elem) {
            $elem.toggleClass("o-todo-complete o-todo-uncomplete").closest("tr").toggleClass("in-todo-complete");
        }

        $(".in-todo-table").bindEvents({
            "click .o-todo-uncomplete": function () {
                var $elem = $(this);

                complete($elem.attr("data-id"), true)
                    .done(function (res) {
                        res.isSuccess && toggleRowState($elem);
                    });
            },

            "click .o-todo-complete": function () {
                var $elem = $(this);

                complete($elem.attr("data-id"), false)
                    .done(function (res) {
                        res.isSuccess && toggleRowState($elem);
                    });
            }
        });

    })();
</script>