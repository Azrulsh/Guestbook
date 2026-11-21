<?php

date_default_timezone_set('Asia/Kuala_Lumpur');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Guest Book</title>
    <style>
        .guestbook-text {
            font-family: Verdana, Arial, Helvetica, sans-serif;
        }

        .guestbook-datetime {
            font-family: Verdana, Arial, Helvetica, sans-serif;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div align="center">
        <table width="379" height="286" border="3" bordercolor="#666666">
            <tr>
                <td height="190" bgcolor="#999999">
                    <p align="center" class="guestbook-text"><strong>My Guestbook</strong></p>
                    <div align="center">
                        <span class="guestbook-datetime">Tarikh : <?= date('d-m-Y') ?></span>
                    </div>
                    <p align="center" class="guestbook-text"></p>
                    <div align="center">
                        <span class="guestbook-datetime">Masa : <?= date('H:i') ?></span>
                    </div>
                    <p align="center" class="guestbook-text">
                        [<a href="masuk.php">Masuk Data Baru</a> | <a href="papar.php">Paparan Data</a> ]
                    </p>
                </td>
            </tr>
        </table>
        <p class="guestbook-text">&nbsp;</p>
    </div>
</body>
</html>
