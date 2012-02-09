<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Inloggad - <?php echo $_SESSION['user']; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<link rel="stylesheet" type="text/css" href="../css/style.css" />
<link type="text/css" href="../css/custom-theme/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
<link href='http://fonts.googleapis.com/css?family=Architects+Daughter|Permanent+Marker|Aclonica|Muli' rel='stylesheet' type='text/css' />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/json.js"></script>
<script type="text/javascript">
	var data = <?php echo json_encode(simplexml_load_file('../rum/'.$currentRum.'data.xml'));?>;
	if(!data) data = {};
	if(!data.nyheter) data.nyheter = {nyhet: []};
	if(!data.nyheter.nyhet) data.nyheter.nyhet = [];
	if(!data.kontakter)data.kontakter = {kontakt: []};
	if(!data.kontakter.kontakt)data.kontakter.kontakt = [];
	if(!data.kalender) data.kalender = {date:[]};
	if(!data.kalender.date) data.kalender.date = [];
	data.rum=[];
	data.rum.delete=[];
	!(data.kontakter.kontakt instanceof Array)&&(data.kontakter.kontakt=[data.kontakter.kontakt]);
	var tab_counter = <?php echo count($SimpleTab); ?>;
</script>
<script type="text/javascript">
	function kontakterClick(t,undefined){
			var k=undefined; 
			if(t!=k){
				var i=$('#kont .ui-button').index($(t));
				k=data.kontakter.kontakt[i];
				$('#namn').val(typeof k.namn == 'object'?"":k.namn);
				$('#image-kont').val(typeof k.img == 'object'?"":k.img);
				$('#mail').val(typeof k.email == 'object'?"":k.email);
				$('#dialog-kontakt').dialog({
					buttons: {
						Redigera: function() {
							var temp=$('#kont .ui-button')[i].getElementsByTagName('span');
							temp[0].innerHTML="<a href='javascript:\"mailto:"+$('#mail').val()+"\";void(0);'>"+$('#mail').val()+"</a>";
							$( "#kontakt-form .kont" ).val("edit"+i);
							$('#kontakt-form')[0].submit();
							$( this ).dialog( "close" );
						},
						'Ta bort': function() {
							$('#kont .ui-button')[i].parentNode.removeChild($('#kont .ui-button')[i]);
							$( "#kontakt-form .kont" ).val("rase"+i);
							$('#kontakt-form')[0].submit();
							$( this ).dialog( "close" );
						},
						Avbryt: function() {
							$( this ).dialog( "close" );
						}
					}
				});
				$('#dialog-kontakt').dialog('open');
			}
			else{
				$('#namn').val("");
				$('#image-kont').val("");
				$('#mail').val("");
				$('#dialog-kontakt').dialog({
					buttons: {
						"Lägg till": function() {
							var temp=$('#kont .ui-button').append("<div class='ui-button ui-corner-all' style='width:100%;border:0px solid #000;padding:2px;text-align:left;' onmouseover='this.className=\"ui-button ui-corner-all ui-state-hover\";' onmouseout='this.className=\"ui-button ui-corner-all\";' ><img width='30' align='left' style='margin-right:3px;' src='"+$('#image-kont').val()+"' onerror='this.src=\"../img/top_bottom.png\";' />"+
								"<span><a href='javascript:\"mailto:"+$('#mail').val()+"\";void(0);'>"+$('#mail').val()+"</a></span><br/>");
							$( "#kontakt-form .kont" ).val("new");
							$('#kontakt-form')[0].submit();
							$( this ).dialog( "close" );
						},
						Avbryt: function() {
							$( this ).dialog( "close" );
						}
					}
				});
				$('#dialog-kontakt').dialog('open');
			}
	}
	$(function(){
		$('#tools').find('button').button();
		$('#mainheader').find('button').button().click(function(){
			window.location="logout.php?rum=<?php echo isset($_GET['rum'])?$_GET['rum']:''; ?>";
		});
		$('#kont .ui-button').click(function(){
			kontakterClick(this);
		});
		$('#accordion').accordion({ header: "h3", autoHeight:false, navigation: true});
		
		/** ------------------------------------------------------------------------**/ 
		/*  Tabs */
		var tab_title_input = $( "#tab_title"),
			tab_content_input = $( "#tab_content" );

		// tabs init with a custom tab template and an "add" callback filling in the content
		var tabs = $( "#tabs").tabs({
			add: function( event, ui ) {
				var tab_title = tab_title_input.val() || "Rubrik - " + tab_counter;
				var tab_content = tab_content_input.val() || "Tab " + tab_counter + " content.";
				data.tabs.tab.push(new Object());
				data.tabs.tab[data.tabs.tab.length-1].rub=tab_title;
				data.tabs.tab[data.tabs.tab.length-1].desk=tab_content;
				$(ui.tab).attr('id','rub'+tab_counter+''+tab_title);
				$(ui.tab).parent().append(
					'<span href="#'+tab_counter+tab_title+'">'+
						'<span class="ui-icon ui-icon-tag" style="float:left;" onclick="//change(event,this.parentNode.parentNode.getElementsByTagName(\'a\')[0]);return false;" title="Redigera" ></span>'+
					'</span><br/>'+
					'<span href="#'+tab_counter+tab_title+'">'+
						'<span class="ui-icon ui-icon-trash" style="float:left;" onclick="remove(this.parentNode.parentNode.getElementsByTagName(\'a\')[0].id);" title="Ta bort"></span>'+
					'</span>'
				);
				/*$(ui.tab).parent().html(
					'<a href="#'+tab_counter+tab_title+'" onclick="$( \'#tabs\').tabs(\'select\', '+tab_counter+1+');return false;" style="float:left;" id="rub'+tab_counter+tab_title+'" >'+
						tab_title+
					'</a>'+
					'<span href="#'+tab_counter+tab_title+'">'+
						'<span class="ui-icon ui-icon-tag" style="float:left;" onclick="change(event,this.parentNode.parentNode.getElementsByTagName(\'a\')[0]);return false;" title="Redigera" ></span>'+
					'</span><br/>'+
					'<span href="#'+tab_counter+tab_title+'">'+
						'<span class="ui-icon ui-icon-trash" style="float:left;" onclick="remove(this.parentNode.parentNode.getElementsByTagName(\'a\')[0].id);console.log(data);" title="Ta bort"></span>'+
					'</span>'
				);*/
				$( ui.panel ).attr('id',""+tab_counter+tab_title);
				$( ui.panel ).click(
					function(event){
						//change(event);
					}
				);
				$( ui.panel ).append( tab_content );
			}
		});

		// modal dialog init: custom buttons and a "close" callback reseting the form inside
		var dd = $( "#dialog-tab" ).dialog({
			autoOpen: false,
			modal: true,
			buttons: {
				Add: function() {
					addTab();
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			},
			open: function() {
				$( "#namn").focus();
			},
			close: function() {
				$( "#namn").parent().reset();
			}
		});
		var dd = $( "#dialog-kontakt" ).dialog({
			autoOpen: false,
			modal: true,
			resizable:false,
			position: 'center',
			buttons: {
				'Lägg till': function() {
					addKontakt();
					//alert();//$("#kontakt-form").submit();
					$( this ).dialog( "close" );
				},
				'Avbryt': function() {
					$( this ).dialog( "close" );
				}
			},
			open: function() {
				$( "#tab_title").focus();
			},
			close: function() {
				form[ 0 ].reset();
				$('#beskr').val("");
			}
		});

		// addTab form: calls addTab function on submit and closes the dialog
		var form = $( "form", dd ).submit(function() {
			addTab();
			dd.dialog( "close" );
			return false;
		});
		/** ------------------------------------------------------------------------**/ 
		
		$('.button').button();
		if(location.href.indexOf('#')!=-1)
			$('#tabs li:first-child a').click();
	});
	var rum={};
	rum.add=function(pa){
		var li = $('<li>');
		li.html('<input type="text" name="'+(pa=="nyheter"?"nyhet":"date")+'[]" /><span style="float:right;" onclick="this.parentNode.outerHTML=\'\';rum.update();" class="del ui-icon ui-icon-trash"></span>');
		if(pa=="nyheter")
			$('#accordion .nyheter').append(li);
		if(pa=="handelse")
			$('#accordion .kalender').append(li);
		li.find("input").focus();
	};
	rum.update=function(){
		function b(){
			$('#beskrivning').html(data.inledning);
		}
		function n(){
			var a=$('#accordion .nyheter li');
			//var b=$('#accordion .nyheter hr');
			if(typeof data.nyheter.nyhet!="string"){
				for(var i=0;i<a.length;i++){
					if(data.nyheter.nyhet[i]!="")
						a[i].innerHTML=data.nyheter.nyhet[i];
					else{
						a[i].parentNode.removeChild(a[i]);
						//b[i].parentNode.removeChild(b[i]);
						data.nyheter.nyhet.splice(i,1);
						n();
						break;
					}
				}
			}
			else if(data.nyheter.nyhet!="")
				a[0].innerHTML=data.nyheter.nyhet;
			else{
				a[0].parentNode.removeChild(a[0]);
				delete data.nyheter.nyhet;
			}
		}
		function k(){
			var a=$('#accordion .kalender li');
			//var b=$('#accordion .kalender hr');
			if(typeof data.kalender.date!="string"){
				for(var i=0;i<a.length;i++){
					if(data.kalender.date[i]!="")
						a[i].innerHTML=data.kalender.date[i];
					else{
						a[i].parentNode.removeChild(a[i]);
						//b[i].parentNode.removeChild(b[i]);
						data.kalender.date.splice(i,1);
						k();
						break;
					}
				}
			}
			else if(data.kalender.date!="")
				a[0].innerHTML=data.kalender.date;
			else{
				a[0].parentNode.removeChild(a[0]);
				delete data.kalender.date;
			}
		}
		function r(i){
			$("#"+i).html(data[i]);
		}
		function u(){
			$("#navigation").parent().prev().find('a').html(data['navigation']);
		}
		function m(j){
			$("#rum"+j).html(
				'<span class="ui-button-text">'+
					'<span class="ui-icon ui-icon-tag" style="float:left;" onclick="//change(event,this.parentNode.parentNode);return false;" title="Redigera"></span> '+
					data.rum[j]+
					'<span onclick="return false;" class="ui-icon ui-icon-trash" style="float:right;" ></span>'+
				'</span>'
			);
		}
		function t(){
			for(var j in data.tabs.tab){
				$("[id='rub"+j+data.tabs.tab[j].rub+"']").html(data.tabs.tab[j].rub);
				var b=$("[id='rub"+j+data.tabs.tab[j].rub+"']").parent().parent().next();
				var c=$("[id='rub"+j+data.tabs.tab[j].rub+"']");
				while(b.attr('id')[0]!=c.attr('id').replace('rub','')[0])
					b=b.next();
				if(b.attr('id')[0]==c.attr('id').replace('rub','')[0])
					b.html(data.tabs.tab[j].desk);
			}
		}
		function e(){
			for(var i in data){
				if(i=="nyheter"){
					n();
				}
				else if(i=="kalender"){
					k();
				}
				else if(i=="kontakter"){
					//TODO: Kontakter uppdatera
				}
				else if(i=="media"){
					//TODO: media uppdatera
				}
				else if(i=="Namn"){//i.indexOf("rub")!=-1
					r(i);
				}
				else if(i=="navigation"){
					r(i);
					u();
				}
				else if(i.indexOf("rum")!=-1){
					for(var j=0;j<data.rum.length;j++)
						if(data.rum[j] != undefined)
							m(j);
				}
				else if(i=="tabs"){
					t();
				}
				else{
					delete data[i];
				}
			}
		}
		if(arguments.length==0){
			//error check
			e();
		}
		else if(arguments.length==1){
			if(arguments[0]==="tabs"){
				t();
			}
		}
	};
	function addTab() {
		var tab_title = $( "#tab_title").val() || "Tab " + tab_counter;
		$('#tabs').tabs( "add", "#" + tab_counter+tab_title, tab_title, tab_counter );
		tab_counter++;
	}
	function addKontakt(){
		/** @TODO image uploading */
		data.kontakter.kontakt.push({
			namn:$('#namn').val(),
			img:$('#image-kont').val(),//img src
			email:$('#mail').val()
		});
		var html="<div class='ui-button ui-corner-all' style='width:100%;border:0px solid #000;padding:2px;text-align:left;' onmouseover='this.className=\"ui-button ui-corner-all ui-state-hover\";' onmouseout='this.className=\"ui-button ui-corner-all\";' >";
		if ($('#image-kont').val())
			html+='<img width="30" align="left" style="margin-right:3px;" src="'+$('#image-kont').val()+'" onerror="this.src=\'../img/top_bottom.png\';" />';
		else
			html+='<img width="30" align="left" style="margin-right:3px;" src="../img/top_bottom.png" />';
		if ($('#mail').val())
			html+='<span><a href="javascript:\'mailto:'+$('#mail').val()+'\';void(0);">'+$('#mail').val()+'</a></span><br/>';
		else html+='<span>Ingen mail-address</span><br/>';
		html+="</div><p/>";
		var i=$('#kont .ui-button').length;
		$('#kont').append(html);
		$($('#kont .ui-button')[i]).click(function(){
			kontakterClick(this);
		});
	}
	function remove(i){
		delete data.tabs.tab[parseInt(i.replace('rub','')[0])];
		var a=$('[id='+i+']');
		/*var b=$('[id='+a.attr('href').replace('#','')+']');
		if(b.css('display')!='none')
		b.remove();
		a.parent().remove();*///alert(a.parent().prev().html()!=null);
		if(a.parent().next().find('a').attr('href')!="#add")
			a.parent().next().find('a').click();
		else if(a.parent().prev().html()!=null)
			a.parent().prev().find('a').click();
		else return;
		$("#tabs").tabs('remove',parseInt(i.replace('rub','')[0]));
		tab_counter--;
	};
	function change(type){ 
		var lists = $('#accordion .'+type+' li');
		if(!lists.data("load")){
			if (lists.length == 0){
				$('#accordion .'+type).html('<li style="padding:10px 0 10px 0;border-bottom:2px groove #ccc;" ><input type="text" name="'+(type=='nyheter'?'nyhet':'date')+'[]" /><span style="float:right;" onclick="this.parentNode.outerHTML=\'\';//rum.update();" class="del ui-icon ui-icon-trash"></span></li>');
			}
			lists.each(function(li){
				var text = lists[li].innerText;
				$(lists[li]).html('<input type="text" value="'+text+'" name="'+(type=='nyheter'?'nyhet':'date')+'[]" /><span style="float:right;" onclick="this.parentNode.outerHTML=\'\';//rum.update();" class="del ui-icon ui-icon-trash"></span>');
			}); 
			lists.data("load",true);
		}
	}
</script>
