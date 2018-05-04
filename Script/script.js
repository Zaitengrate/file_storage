$('th').click(function()
{
    var text = '';
    var table = $(this).parents('table');
    var rows = table.find('tr:gt(0)').toArray();
    var sorted_rows = rows.sort(comparator($(this).index()));
    var row_name = $(this).html();
    if (row_name.search(' A-z') !== -1) {
        rows = rows.reverse();
        $(this).html(row_name.replace('A-z', 'z-A'))
    } else {
        text = row_name+' A-z';
        row_name.search('z-A') !== -1 ? $(this).html(row_name.replace('z-A', 'A-z')) : $(this).html(text);

    }
    for (var i = 0; i < rows.length; i++){
        table.append(sorted_rows[i])
    }
});
function comparator(index)
{
    return function(a, b)
    {
        var A = $(a).children('td').eq(index).text();
        var B = $(b).children('td').eq(index).text();
        return $.isNumeric(A) && $.isNumeric(B) ? A - B : A.toString().localeCompare(B)
    }
}

$(document).on('click', 'td.delete', function(e)
{
    e.preventDefault();
    var url = $(this).children().attr('href');
    var parent = $(this).parent();
    $.ajax({
        url: 'ajax.php'+url,
        type: 'GET',
        success: function(res) {
            if (res === 'Deleted') {
                setTimeout(function() {
                    parent.remove();
                }, 1000);
                parent.animate({
                    backgroundColor: "#aa0000",
                    color: "#fff"
                }, 1000 );
                stat();
            }
            $('#message').html(res);
        }
    });
});

$('#upload').submit(function(e)
{
    e.preventDefault();
    var file_data = $('#upload_file').prop('files')[0];
    var filename = file_data.name;
    var size = (file_data.size/1024).toFixed(2);
    var fd = new FormData();
    fd.append('upload_file', file_data);
    $.ajax({
        url: 'ajax.php',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function(res){
            if (res === 'File uploaded') {
                append_row(size, filename);
                stat();
            }
            $('#message').html(res);
        }
    });
});

$('#download').submit(function(e)
{
    var state = $('#home').is(':checked');
    var location = $('#home').val();
    var url = $('#download_file').val();
    var filename = url.substring(url.lastIndexOf('/')+1);
    var size;
    if (state) {
        e.preventDefault();

        var fd = new FormData();
        fd.append("file_to_download", url);
        fd.append("location", location);

        $.ajax({
            url: 'ajax.php',
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function(res){
                if (res === 'File uploaded') {
                    $.ajax({
                        type: 'HEAD',
                        url: url,
                        complete: function(head){
                            size = (head.getResponseHeader('Content-Length')/1024).toFixed(2);
                            append_row(size, filename);
                        }
                    });
                    stat();
                }
                $('#message').html(res);
            }
        });

    }
});

function stat()
{
    $.ajax({
        url: 'ajax.php?statistic=true',
        type: 'GET',
        success: function(res) {
            var json = JSON.parse(res);
            $('#used_space').html(json.folder);
            $('#nmb_of_files').html(json.files.nmb_of_files);
            $('#size_of_files').html(json.files.size_of_files);
            $('#avg_size_of_files').html(json.files.avg_size_of_files);
        }
    });
}

function append_row(size, name)
{
	$("#table").find('tbody')
                    .append($('<tr style="background-color: green">').animate({backgroundColor: "rgb(40, 40, 40)", color: "#fff"}, 1000)
                        .append($('<td>')
                            .text(name)

                        ).append($('<td>')
                                .text(size)
                        ).append($('<td>')
                            .append($('<a>')
                                .attr('href', '?download=true&file='+name)
                                .text('Download')
                            )
                        ).append($('<td>').addClass('delete')
                            .append($('<a>')
                                .attr('href', '?delete=true&file='+name)
                                .text('Delete')
                            )
                        )
                    );
}