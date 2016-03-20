<?php
namespace Marmalade\Views;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** View for displaying install information to the user */
class InstallView extends View {
    /** 
     * Build the output
     *
     * @param Marmalade\Model $model The model passed from the controller
     *
     * @return string The output to send to the client
     */
    function build_output($model) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
            <title>Marmalade - Install</title>
            <style type="text/css">
                *, *:before, *:after { margin: 0; padding: 0; box-sizing: inherit; }
                html { box-sizing: border-box; }
                body { 
                    background: #F3F3F3; 
                    font-family: helvetica, sans; 
                    color: #333333;
                    line-height: 1.3em;
                }
                a { color: inherit; word-wrap: break-word; }
                h1 { font-weight: 400; margin-bottom: 20px; text-align: center; }
                p { margin: 1em 0px; }
                h3 { 
                    font-weight: 400; 
                    border-bottom: 1px solid #DDDDDD; 
                    padding-bottom: 2px; 
                    margin-bottom: 12px; 
                }
                code {
                    margin: 1em 0px;
                    display: block;
                    overflow: auto;
                    white-space: nowrap;
                }
                .page-wrapper { 
                    min-height: 100%;
                    padding: 70px 10px;
                }
                .content-wrapper {
                    padding: 20px;
                    box-shadow: 0px 0px 5px rgba(0,0,0,.25);
                    background: #FAFAFA;
                    max-width: 700px;
                    margin: auto;
                    border-radius: 10px;
                }
                .section-cont { margin: 50px 0px; }
                .section-cont--keys { word-wrap: break-word; }
                .status--good { color: hsl(120, 75%, 25%); }
                .status--unkown { color: hsl(25, 70%, 50%); }
                .status--bad { color: hsla(0,60%,40%,1); }
                .note { font-style: italic; font-size: .8em; font-weight: 600; }
                .sub-text { font-size: .7em; vertical-align: middle; }
            </style>
        </head>
        <body>
            <div class="page-wrapper">
                <div class="content-wrapper">
                    <h1>Marmalade v<?php echo $model->info->version; ?></h1>
                    <div class="section-cont section-cont--welcome">
                        <h3>Welcome</h3>
                        Welcome to Marmalade! If you're seeing this page you have
                        already completed most of the installation process! All that
                        remains is for you to put the following generated keys in the
                        Constants class and decide if you want to install the
                        optional database and cron-tab.
                    </div>
                    <div class="section-cont section-cont--server-config">
                        <h3>Server Configuration</h3>
                        In order for Marmalade's routing to work properly, you must
                        configure your web server to send all requests through 
                        <em>index.php</em>. This is called a front-controller pattern.
                        There are example configuration files for Apache and 
                        Nginx located in the <em>/app/Config/examples/</em> folder.
                    </div>
                    <div class="section-cont section-cont--keys">
                        <h3>Keys</h3>
                        <?php if (NONCE_KEY === "" || ENCRYPTION_KEY === "") { ?>
                            <p>
                                Please place the following keys into \App\Config\Constants.php.
                            </p>
                        <?php } ?>
                        Nonce Key: <?php echo (NONCE_KEY !== "") ? "<span class='status--good'>Good</span>" : "{$model->nonce_key}"; ?>
                        <?php echo (NONCE_KEY !== "" && ENCRYPTION_KEY !== "") ? "<br>" : "<br><br>" ; ?>
                        Encryption Key: <?php echo (ENCRYPTION_KEY !== "") ? "<span class='status--good'>Good</span>" : "{$model->encryption_key}"; ?>
                    </div>
                    <div class="section-cont section-cont--database">
                        <h3>Database <span class="sub-text">(optional)</span></h3>
                        Connection: 
                        <?php 
                        if ($model->database_connection === 1) { ?>
                            <span class='status--good'>Connected</span>
                            <br>
                            Schema:
                            <?php if ($model->tables_created === 1) { ?>
                                <span class='status--good'>Installed</span>
                            <?php } else if ($model->tables_created === -1) { ?>
                                <span class='status--bad'>Error</div>
                            <?php } else { ?>
                                <span class='status--good'>Already installed</span>
                            <?php } ?>
                        <?php } else if ($model->database_connection === -1) { ?>
                            <span class='status--bad'>Not connected</span>
                            <p>
                                Error connecting to the database. Please check your 
                                Database constants in \App\Config\Constants.php.
                            </p>
                        <?php } else if ($model->database_connection === 0) { ?>
                            <span class='status--unkown'>Not configured</span>
                        <?php } ?>
                    </div>
                    <div class="section-cont section-cont--cron">
                        <h3>Cron <span class="sub-text">(optional)</span></h3>
                        <p>
                            To install a cron-tab to run Marmalade cron jobs, use
                            the following:<br>
                            <span class='note'>
                                (this cron-tab should especially be installed if either the XHR_DATABASE_NONCES or 
                                API_DATABASE_NONCES constants are enabled)
                            </span>
                        </p>
                        <code>
                            */5 * * * *&nbsp;&nbsp;&nbsp;&nbsp;php <?php echo ROOT_DIR; ?>/cron.php five_minutes<br>
                            */30 * * * *&nbsp;&nbsp;&nbsp;php <?php echo ROOT_DIR; ?>/cron.php thirty_minutes<br>
                            0 * * * *&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;php <?php echo ROOT_DIR; ?>/cron.php one_hour<br>
                            0 */6 * * *&nbsp;&nbsp;&nbsp;&nbsp;php <?php echo ROOT_DIR; ?>/cron.php six_hours<br>
                            0 */12 * * *&nbsp;&nbsp;&nbsp;php <?php echo ROOT_DIR; ?>/cron.php twelve_hours<br>
                            0 0 * * *&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;php <?php echo ROOT_DIR; ?>/cron.php one_day<br>
                        </code>
                    </div>
                    <div class="section-cont section-cont--documentation">
                        <h3>Documentation</h3>
                        For more documentation, visit: <a href="https://ethossoftworks.com/projects/marmalade" target="_blank">https://ethossoftworks.com/projects/marmalade</a>.
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}