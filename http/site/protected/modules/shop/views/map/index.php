<?php $this->pageTitle = "OUTDOOR КАРТА";?>
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/easyXDM.min.js"></script>
<script type="text/javascript">
    /**
     * Request the use of the JSON object
     */
    easyXDM.DomHelper.requiresJSON("<?=Yii::app()->theme->baseUrl?>/js/json2.js");
</script>
<h2>Outdoor <strong>карта</strong></h2>
<div class="info clearfix staticpage">
    <div class="content">
        <div id="embedded" style="border: 0px; width:100%; height:800px;">
        </div>
        <!-- <iframe src="http://mir.1gb.ru/catalog/?w=938&amp;h=650" style="border: 0px; width:100%; height:800px;"> -->
    </div>
</div>
<script type="text/javascript">


    var remote = new easyXDM.Rpc(/** The channel configuration */{
        /**
         * Register the url to hash.html, this must be an absolute path
         * or a path relative to the root.
         * @field
         */
        local: "/assets/name.html",
        swf: "http://mir-map.neo-systems.ru/bitrix/templates/mir/js/easyxdm.swf",
        /**
         * Register the url to the remote interface
         * @field
         */
        remote: "http://mir-map.neo-systems.ru/catalog/?w=938&amp;h=650",
        remoteHelper: "http://mir-map.neo-systems.ru/bitrix/templates/mir/js/name.html",
        /**
         * Register the DOMElement that the generated IFrame should be inserted into
         */
        container: "embedded",
        props: {
            style: {
                border: "none",
                height: "800px",
                width: "100%"
            }
        }

    }, /** The interface configuration */ {
        remote: {
            //
        },
        local: {
            alertMessage: function(msg){
                alert(msg);
            }
        }
    });
</script>