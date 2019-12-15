<?php
$tab = trim($_GET['tab'] ?? 'table');
if (!in_array($tab, ['table', 'about'])) {
    $tab = 'table';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo ucfirst($tab); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <style>
        .header-btn {
            margin: 5px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container">
    <div class="col-sm-12 text-center">
        <a class="btn btn-info header-btn" href="index.php?tab=table">Table</a>
        <a class="btn btn-info header-btn" href="index.php?tab=about">About</a>
    </div>
    <div class="col-sm-12 text-center">
        <?php
            if ('about' === $tab) {
                ?>
                <iframe width="560" height="315" src="https://www.youtube.com/embed/C3k1h1VA7iM" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <?php
            }
            if ('table' === $tab) {
                ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>Title</td>
                            <td>Content</td>
                            <td>Author</td>
                            <td>Actions</td>
                        </tr>
                    </thead>
                    <tbody id="table-content"></tbody>
                </table>
                <span class="btn btn-info" id="btn-prev">Prev</span> <span class="btn btn-info" id="btn-next">Next</span>
                <script>
                    var page = 0;
                    var limit = 25;

                    $(document).ready(function () {
                        loadData();

                        $('#btn-prev').click(function () {
                            if (page > 0) {
                                page--;
                            }
                            loadData();
                        })
                        $('#btn-next').click(function () {
                            page++;
                            loadData();
                        })
                    });

                    function updateTable(newRows) {
                        $('#table-content').empty();
                        newRows.forEach(function(item){
                            var element = '<tr><td>' + item.id + '</td><td>' + item.headline
                                + '</td><td>' + item.content  + '</td><td>' + item.user.username
                                + '</td><td><span class="btn btn-danger delete-row" data-id="' + item.id
                                + '">Delete record</span></td></tr>';
                            $('#table-content').append(element);
                        });
                        $('.delete-row').each(function () {
                            $(this).click(function () {
                                $.ajax({
                                    url: 'delete.php',
                                    method: 'GET',
                                    data: {id: $(this).data('id')},
                                    success: function(){
                                        loadData();
                                    }
                                });
                            })
                        });
                    }

                    function loadData() {
                        $.ajax({
                            url: 'loader.php',
                            method: 'GET',
                            data: {page: page, limit: limit},
                            success: function(data){
                                updateTable(data);
                            }
                        });
                    }
                </script>
                <?php
            }
        ?>

    </div>
</div>
</body>
</html>

