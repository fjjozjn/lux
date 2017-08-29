<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>
    <link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css">
    <script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
    <script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
</head>
<body>

<div data-role="page" id="pageone">
    <div data-role="header" data-theme="b">
        <h1>PAY</h1>
    </div>

    <div data-role="content">
        <form method="post" action="mol_points.php">
            <fieldset data-role="collapsible" data-theme="b" data-content-theme="d">
                <legend>Mol Points</legend>
                <label for="amount">Amount : </label>
                <select name="amount" id="amount">
                    <option value="1">1</option>
                    <option value="10">10</option>
                </select>

                <label for="currency">Currency : </label>
                <select name="currency" id="currency">
                    <option value="VND">VND</option>
                </select>

                <input type="submit" data-inline="true" value="Submit">
            </fieldset>
        </form>
<!--
        <div data-role="collapsible">
                    <h1>Mol Coupon</h1>
                    <form method="post" action="mol_points.php">
                        <label for="amount">Amount : </label>
                        <select name="amount" id="amount">
                            <option value="1">1</option>
                            <option value="10">10</option>
                        </select>

                        <label for="currency">Currency : </label>
                        <select name="currency" id="currency">
                            <option value="VND">VND</option>
                        </select>

                        <input type="submit" data-inline="true" value="Submit">
                    </form>
                </div>-->
    </div>

    <div data-role="footer" data-theme="b">
        <h1>© <?php echo date('Y'); ?> CUPLAY, INC. ALL RIGHTS RESERVED. </h1>
    </div>
</div>

</body>
</html>
