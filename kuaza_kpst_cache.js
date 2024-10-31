var idler = gosterjskpst.post_id; // konu id ilerini aliriz..
var eklentiyolu = gosterjskpst.eklentiyolu; // konu id ilerini aliriz..
var idler2 = JSON.parse(idler); // json ile cozeriz
var i = "0";
var extrazaman = "200";

idler2.forEach(function(entry) { // Allah ne verdiyse donguye sokariz
i++;

var gecikmeekleyelim = (i * extrazaman);
var yeniyuklenmezamani = parseInt(gosterjskpst.settimeouttime)+parseInt(gecikmeekleyelim);

 setTimeout(function(){ // adminden belirlenen zamana gore yuklenirken gecikme eklettiririz.
	  jQuery.ajax(
	  {
	  type:'GET',
	  url:gosterjskpst.admin_ajax_url,
	  dataType:"html",
	  data:"post_id="+entry+"&action=kpst_social_counter_ajax",
                beforeSend:function(){
					$content = jQuery("#kpst_social_counter_"+entry);
                        $content.append('<div id="yukleniyorikonu" class="yukleniyorikonu"><img src="'+eklentiyolu+'/img/ajax_loader.gif" /></div>');
                },
                success:function(cevap_kpst){
					$content = jQuery("#kpst_social_counter_"+entry);
                    if(cevap_kpst.length){
                        $content.hide();
                        $content.append(cevap_kpst);
                        $content.fadeIn(0, function(){
                            jQuery("#yukleniyorikonu").remove();
                            loading = false;
                        });
                    } else {
                        jQuery("#yukleniyorikonu").remove();
                    }
                },
                error     : function(jqXHR, textStatus, errorThrown) {
                    jQuery("#yukleniyorikonu").remove();
                    console.log(jqXHR + " :: " + textStatus + " :: " + errorThrown);
                }
	  });
 },yeniyuklenmezamani);

});