var weeks_name = ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];
var months_name = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентярь','Октябрь','Ноябрь','Декабрь'];
var months_nday = [31,0,31,30,31,30,31,31,30,31,30,31];

var c_purchased = [['1.06.2011','2.07.2011']];
var c_reserved  = [['13.07.2011','20.07.2011']];

function click_m(coord){
	if (all_selected[0]>=0){
		if (all_selected[1]>=0){
			all_selected = [coord,-1];
			end_m(coord);
		}else{
			document.getElementById('day_'+coord).className='tdc tdc_navend';
			all_selected[1] = coord;
		}
	}else{
		all_selected[0] = coord;
	}
	return false;
}

function end_m(coord){
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
		}
	}
}


function fill_table(month,month_length) {
	var result='';
	day=1;
	result+="";
	result+="<TD class=\"tdc tdc_header\">"+month+" "+year+"</TD>";
	


	while (day <= month_length) {
		if (all_day_count < day_skip){
			result+="<TD ALIGN=center class=\"tdc tdc_navoffline\" id=\"day_"+all_day_count+"\"></TD>";
		}else{
			result+="<TD ALIGN=center class=\"tdc tdc_navoff\" id=\"day_"+all_day_count+"\" onmouseover=\"end_m("+all_day_count+");\" onclick=\"return click_m("+all_day_count+");\"></TD>"
		}
		all_day_count++
		day++
	}
	return result;
}

function return_day_from_dates(values){
	var result = new Array(-1,-1);
	var tdate1 = new Date(values[0].replace(/(\d+)\.(\d+)\.(\d+)/, '$2/$1/$3'));
	var tdate2 = new Date(values[1].replace(/(\d+)\.(\d+)\.(\d+)/, '$2/$1/$3'));
	if (tdate2 > today){
		result[0] = (tdate1 > today) ? Math.ceil((tdate1 - today) / (1000 * 60 * 60 * 24))+day_skip : day_skip;
		result[1] = Math.ceil((tdate2 - today) / (1000 * 60 * 60 * 24))+day_skip;
	}
	return result;
}

function create_calendar(){
	
	var result='';
	today = new Date();

	all_selected = [-1,-1];
	all_day_count = 0;
	all_day_array = new Array(400);
	c_purchased2 = new Array();
	c_reserved2 = new Array();

	day_skip= today.getDate();
	year	= today.getYear();
	mon	= today.getMonth();
	if (year < 2000) year = year + 1900; 

	for (var i=0;i<c_purchased.length;i++){
		c_purchased2[i] = return_day_from_dates( c_purchased[i] );
	}
	for (var i=0;i<c_reserved.length;i++){
		c_reserved2[i] = return_day_from_dates( c_reserved[i] );
	}

	
	result+="<TABLE WIDTH=0 BORDER=0 CELLSPACING=2 CELLPADDING=2 class=\"tablec\"><TR><TD>&nbsp;</TD>"
	for (var i=1;i<32;i++){
		result+="<TD class=\"tdc tdc_header\">"+i+"</TD>";
	}
	result+="</TR><TR>"

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
		mon++;
	}

	result+="</TABLE>"
	return result
}
