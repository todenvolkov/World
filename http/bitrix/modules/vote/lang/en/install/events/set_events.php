<?
$MESS ['VOTE_NEW_NAME'] = "New voting";
$MESS ['VOTE_NEW_DESC'] = "
#ID# - vote result ID
#TIME# - time of voting
#VOTE_TITLE# - vote title
#VOTE_DESCRIPTION# - vote description
#VOTE_ID# - vote ID
#CHANNEL# - group name
#CHANNEL_ID# - group ID
#VOTER_ID# - guest ID
#USER_NAME# - user name
#LOGIN# - login
#USER_ID# - user ID
#STAT_GUEST_ID# - guest ID of statistics module
#SESSION_ID# - session ID of statistics module
#IP# - IP address
";
$MESS ['VOTE_NEW_SUBJECT'] = "#SITE_NAME#: New voting for vote \"[#VOTE_ID#] #VOTE_TITLE#\"";
$MESS ['VOTE_NEW_MESSAGE'] = "
New voting

Vote       - [#VOTE_ID#] #VOTE_TITLE#
Group      - [#CHANNEL_ID#] #CHANNEL#

--------------------------------------------------------------

Guest      - [#VOTER_ID#] (#LOGIN#) #USER_NAME# [#STAT_GUEST_ID#]
Session    - #SESSION_ID#
IP address - #IP#
Time       - #TIME#

To view results of this voting visit link:
http://#SERVER_NAME#/bitrix/admin/vote_user_results.php?EVENT_ID=#ID#&lang=ru


Ti view results diagram of vote visit link:
http://#SERVER_NAME#/bitrix/admin/vote_results.php?lang=ru&VOTE_ID=#VOTE_ID#

Automatically generated message.
";
?>