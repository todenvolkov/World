var weeks_name = ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];
var months_name = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентярь','Октябрь','Ноябрь','Декабрь'];
var months_nday = [31,0,31,30,31,30,31,31,30,31,30,31];
var millisecond = 1000 * 60 * 60 * 24;
var c_periods	= new Array();
var c_request;
var c_request_array = new Array();
var c_request_id= 0;
var c_request_price= 0;

function clear_period(num){
	for (var i=c_periods[num][0];i<=c_periods[num][1];i++){
		all_day_array[i] = null;
		document.getElementById('day_'+i).className='tdc tdc_navoff';
	}
	c_periods.splice(num,1);
	redraw_periods();
	return false;
}
function redraw_periods(){
	c_request_array = new Array();
	var green = 0;
	var t_content = "";
	var aa = new Date;
	var bb = new Date;
	var today_ms = Math.floor(today)+21600000;
	for (var i=0;i<c_periods.length;i++){
		aa.setTime( today_ms + millisecond*(c_periods[i][0]-day_skip+1) );
		bb.setTime( today_ms + millisecond*(c_periods[i][1]-day_skip+1) );
		t_content+="<TR><TD class=\"tdc\" width=\"180\" style=\"text-align: left;\">&nbsp;"+aa.toLocaleDateString().replace(/ /g,"&nbsp;")+"&nbsp;</TD><TD class=\"tdc\" width=\"180\" style=\"text-align: left;\">&nbsp;"+bb.toLocaleDateString().replace(/ /g,"&nbsp;")+"&nbsp;</TD><TD class=\"tdc\"><IMG SRC=\"/bitrix/templates/mir/images/Del.png\" width=\"12\" height=\"12\" border=\"0\" onclick=\"javascript:return clear_period("+i+");\"></TD></TR>";
		green+= c_periods[i][1] - c_periods[i][0] + 1;
		
		//c_request_array.push("&COUNT="+(c_periods[i][1] - c_periods[i][0] + 1)+"&DATE1="+aa.getDate()+"."+(aa.getMonth()+1)+"."+aa.getFullYear()+"&DATE2="+bb.getDate()+"."+(bb.getMonth()+1)+"."+bb.getFullYear());
		c_request_array.push( [ (c_periods[i][1] - c_periods[i][0] + 1 ), aa.getDate()+"."+(aa.getMonth()+1)+"."+aa.getFullYear(), bb.getDate()+"."+(bb.getMonth()+1)+"."+bb.getFullYear() ] );
		
	}
	document.getElementById('tdc_status2').innerHTML = 'Выбрано&nbsp;дней:&nbsp;'+green+'&nbsp;&nbsp;&nbsp;Полная&nbsp;стоимость:&nbsp;'+Math.round(c_request_price*green);
	document.getElementById('tdc_status3').innerHTML = "<TABLE WIDTH=360 BORDER=0 CELLSPACING=0 CELLPADDING=2 class=\"tablec\">"+t_content+"</TABLE>";
	document.getElementById('to_basket').disabled = (c_periods.length==0)?true:false;
}
function click_m(coord){
	if (all_selected[0]>=0){
		if (all_selected[1]>=0){
			if (c_periods.length>=5){
				alert('Вы можете выбрать максимум 5 периодов!');
			}else{
				all_selected = [coord,-1];
				end_m(coord);
			}
		}else{
			document.getElementById('day_'+coord).className='tdc tdc_navend';
			all_selected[1] = coord;

			c_periods = new Array();
			var t_periods = null;
			for (var i=0;i<all_day_count;i++){
				if (all_day_array[i]==1 || all_day_array[i]==2){
					all_day_array[i] = 2;
					document.getElementById('day_'+i).className='tdc tdc_navon';
					if (t_periods==null){
						t_periods = i;
						document.getElementById('day_'+i).className='tdc tdc_navstart';
					}
					if (all_day_array[i+1]!=1 && all_day_array[i+1]!=2){
						c_periods.push([t_periods,i]);
						t_periods = null;
						document.getElementById('day_'+i).className='tdc tdc_navstart';
					}
				}
			}
			redraw_periods();
		}
	}else{
		all_selected = [coord,-1];
		end_m(coord);
	}
	return false;
}

function end_m(coord){
	var green = 0;
	if (all_selected[0]>=0 && all_selected[1]==-1){
		for (var i=0;i<all_day_count;i++){
			if ((all_selected[0]<=i && i<=coord) || (all_selected[0]>=i && i>=coord)){
				if (all_day_array[i]==null || i==all_selected[0]){
					if (i==all_selected[0]){
						document.getElementById('day_'+i).className='tdc tdc_navstart';
					}else{
						document.getElementById('day_'+i).className='tdc tdc_navon';
					}
		  			all_day_array[i]=1;
				}
			}else{
				if (all_day_array[i]==1){
					if (i==all_selected[0]){
						document.getElementById('day_'+i).className='tdc tdc_navstart';
					}else{
						document.getElementById('day_'+i).className='tdc tdc_navoff';
					}
					all_day_array[i]=null;
				}
			}
			if (all_day_array[i]==1 || all_day_array[i]==2) green++
		}
		document.getElementById('tdc_status2').innerHTML = 'Выбрано&nbsp;дней:&nbsp;'+green+'&nbsp;&nbsp;&nbsp;Полная&nbsp;стоимость:&nbsp;'+Math.round(c_request_price*green);
	}
}


function fill_table(month,month_length) {
	var result='';
	day=1;
	result+="";
	result+="<TD class=\"tdc tdc_header\" style=\"text-align: left;\">"+month+"&nbsp;"+year+"</TD>";
	

	while (day <= month_length) {
		if (all_day_count < day_skip){
			result+="<TD class=\"tdc tdc_navoffline\" id=\"day_"+all_day_count+"\"></TD>";
		}else if (all_day_array[all_day_count]==3){
 			result+="<TD class=\"tdc tdc_purchased\" id=\"day_"+all_day_count+"\"></TD>";
		}else if (all_day_array[all_day_count]==4){
			result+="<TD class=\"tdc tdc_reserved\" id=\"day_"+all_day_count+"\"></TD>";
		}else{
			result+="<TD class=\"tdc tdc_navoff\" id=\"day_"+all_day_count+"\" onmouseover=\"end_m("+all_day_count+");\" onclick=\"return click_m("+all_day_count+");\"></TD>"
		}
		all_day_count++
		day++
		if (day%10==0) result+="<TD style=\"width: 1px;\"></TD>"
	}
	while (day <= 31) { result+="<TD class=\"tdc_header\"></TD>"; day++; }
	return result;
}

function fill_all_day_array(type,values){
	//var tdate1 = new Date(values[0].replace(/(\d+)\.(\d+)\.(\d+).*/, '$2/$1/$3'));
	//var tdate2 = new Date(values[1].replace(/(\d+)\.(\d+)\.(\d+).*/, '$2/$1/$3'));
	var tdate0 = Math.floor(today)+21600000;
	var tdate1 = Math.floor(new Date(values[0]*1000))+21600000; //+6 hours
	var tdate2 = Math.floor(new Date(values[1]*1000))+21600000; //+6 hours
	
	if (tdate2 > tdate0){
		var t1 = (tdate1 > tdate0) ? Math.floor((tdate1 - tdate0) / millisecond) + day_skip-1 :  +day_skip-1;
		var t2 = Math.floor((tdate2 - tdate0) / millisecond) + day_skip-1;

		for (var i=t1;i<=t2;i++){
			all_day_array[i] = type;
		}
	}
}

function create_calendar(c_id,c_price,c_purchased,c_reserved){
	
	var result='';
	
	c_request_id = c_id;
	c_request_price = c_price;
	c_periods = new Array();
	c_request_array = new Array();
	
	document.getElementById('to_basket').disabled = true;

	
	all_selected = [-1,-1];
	all_day_count = 0;
	all_day_array = new Array(400);

	today = new Date();
	day_skip= today.getDate();
	year	= today.getFullYear();
	mon	= today.getMonth();
	today = new Date(year,mon,day_skip,0,0,0,0);

	for (var i=0;i<c_purchased.length;i++){
		fill_all_day_array(3,c_purchased[i]);
	}
	
	for (var i=0;i<c_reserved.length;i++){
		fill_all_day_array(4,c_reserved[i]);
	}

	
	result+="<CENTER><TABLE WIDTH=0 BORDER=0 CELLSPACING=0 CELLPADDING=2 class=\"tablec\"><TR><TD class=\"tdc tdc_header\"><B>Выберите&nbsp;период&nbsp;размещения&nbsp;рекламы:</B></TD></TR></TABLE><TABLE WIDTH=0 BORDER=0 CELLSPACING=0 CELLPADDING=2 class=\"tablec\"><TR><TD>&nbsp;</TD>"
	for (var i=1;i<32;i++){
		if (i%10==0) result+="<TD></TD>"
		result+="<TD class=\"tdc tdc_header\">"+i+"</TD>"
	}
	result+="</TR>"

	for (var i=0;i<12;i++){
		if (mon>11){
			mon=0;
			year++;
		}
		if (mon==1){
			if (((year % 4)==0) && ((year % 100)!=0) || ((year % 400)==0)){
				months_nday[mon] = 29;
			}else{
				months_nday[mon] = 28;	
			} 
		}
		result+="<TR>"+fill_table(months_name[mon],months_nday[mon])+"</TR>"
		
		if ((i+1)%3==0) result+="<TR><TD height=4 class=tdc_header></TD><TD height=4 colspan=9 class=tdc_header></TD><TD height=4 class=tdc_header></TD><TD height=4 colspan=9></TD><TD height=4 class=tdc_header></TD><TD height=4 colspan=9></TD><TD height=4 class=tdc_header></TD><TD height=4 colspan=2></TD></TR>"
		mon++;
	}

	result+=""+
	"<TR><TD>&nbsp;</TD><TD colspan=\"34\" align=\"left\">"+
	"<TABLE WIDTH=460 BORDER=0 CELLSPACING=0 CELLPADDING=2 class=\"tablec\">"+
	"<TR>"+
	"<TD class=\"tdc tdc_reserved\"><img src=\"/bitrix/templates/mir/images/blank.gif\" width=\"18\" height=\"1\"></TD>"+
	"<TD class=\"tdc tdc_header\">&nbsp;Забронировано&nbsp;</TD><TD>&nbsp;</TD>"+
	"<TD class=\"tdc tdc_purchased\"><img src=\"/bitrix/templates/mir/images/blank.gif\" width=\"18\" height=\"1\"></TD>"+
	"<TD class=\"tdc tdc_header\">&nbsp;Занято&nbsp;</TD>"+
	"<TD class=\"tdc tdc_header\"></TD>"+
	"<TD class=\"tdc tdc_header\" id=\"tdc_status2\" style=\"width:110px;\">Выбрано&nbsp;дней:&nbsp;0&nbsp;&nbsp;&nbsp;Полная&nbsp;стоимость:&nbsp;0</TD>"+
	"</TR>"+
	"</TABLE><br>"+
	"<DIV align=\"left\" id=\"tdc_status3\" nowrap>Выберите период на календаре</DIV>"+
	"</TD></TR></TABLE>";

	return result
}
