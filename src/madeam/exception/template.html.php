<style>
/* Reset
-----------------------------------------------------------------------------*/
body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,form,fieldset,input,textarea,p,blockquote,th,td { margin:0;padding:0; } table { border-collapse:collapse; border-spacing:0; } fieldset,img { border:0; } address,caption,cite,code,dfn,em,strong,th,var { font-style:normal;  font-weight:normal; } ol,ul {  list-style:none; } caption,th {  text-align:left; } h1,h2,h3,h4,h5,h6 { font-size:100%;  font-weight:normal; } q:before,q:after { content:''; } abbr,acronym { border:0; }


/* Document
-----------------------------------------------------------------------------*/
body {
  font: 62.5%/1.5 "Lucida Grande", "Lucida Sans", Tahoma, Verdana, sans-serif;
  color: #333333;
  background: #fafbfb;
  text-align: center;
}


/* Typography
-----------------------------------------------------------------------------*/
a:active, a:visited {
  color: #444;
}
a:hover {
  color: #000;
}

p, h1, h2, h3, h4, h5, ul, ol, table, blockquote, code {
  margin-top: 1em;
}

hr {
  border: none;
  height: 1px;
  background-color: #ccc;
}

strong, h1, h2, h3, h4, h5 {
  font-weight: 700;
}

em {
  font-style: italic;
}

h1 {
  font-size: 2.2em;
}

h1.debug {
  color: #b43838;
}

h2 {
  font-size: 2.0em;
}

h3 {
  font-size: 1.8em;
}

h4 {
  font-size: 1.6em;
}

h4 + p { margin-top: 0; }

h5 {
  font-size: 1.4em;
}

p {
  font-size: 1.4em;
}

pre, code {
  font-size: 1.4em;
}

ul, ol, dl {
  list-style: none;
  font-size: 1.4em;
}

li, dd, dt {
  font-size: 1.4em;
}

/* Structural
-----------------------------------------------------------------------------*/
.fullspan {
  text-align: left;
  margin: 0 10px 0 10px;
}

.wrapper {
  max-width: 960px;
  margin: 0 auto;
}


/* Code
-----------------------------------------------------------------------------*/
code {
  background: #f1f4fa;
  padding: 1em;
  display: block;
  border: 1px solid #bfc9d9;
  font-family: Monaco, Verdana, Sans-serif;
  color: #253248;
  font-size: 1.2em;
}
</style>

<!DOCTYPE html>
<html>
<head>
  <title>Debugging</title>
</head>
<body>
  <div id="content" class="fullspan debug">

    <h1 class="debug">Oops, <?php echo $message['snippet']; ?></h1>
    <h4><?php echo $message['exception']; ?> Exception:</h4>
    <p><?php echo $message['error']; ?></p>

    <br />
    <br />

    <hr />
    <p><strong><?php echo $message['file']; ?></strong> on line <strong><?php echo $message['line']; ?></strong></p>

    <h4>Backtrace:</h4>
    <pre class="backtrace"><?php echo $message['backtrace']; ?></pre>

    <br />
    <p><em><strong>Note</strong>: You can disable these error messages in application/config/setup.php</em></p>

  </div>

</body>
</html>