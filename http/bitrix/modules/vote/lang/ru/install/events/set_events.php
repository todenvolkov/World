<?
$MESS ['VOTE_NEW_NAME'] = "Новое голосование";
$MESS ['VOTE_NEW_DESC'] = "#ID# - ID результата голосования
#TIME# - время голосования
#VOTE_TITLE# - наименование опроса
#VOTE_DESCRIPTION# - описание опроса
#VOTE_ID# - ID опроса
#CHANNEL# - наименование группы опроса
#CHANNEL_ID# - ID группы опроса
#VOTER_ID# - ID проголосовавшего посетителя
#USER_NAME# - ФИО пользователя
#LOGIN# - логин
#USER_ID# - ID пользователя
#STAT_GUEST_ID# - ID посетителя модуля статистики
#SESSION_ID# - ID сессии модуля статистики
#IP# - IP адрес
";
$MESS ['VOTE_NEW_SUBJECT'] = "#SITE_NAME#: Новое голосование по опросу \"[#VOTE_ID#] #VOTE_TITLE#\"";
$MESS ['VOTE_NEW_MESSAGE'] = "Новое голосование по опросу

Опрос       - [#VOTE_ID#] #VOTE_TITLE#
Группа      - [#CHANNEL_ID#] #CHANNEL#

--------------------------------------------------------------

Посетитель  - [#VOTER_ID#] (#LOGIN#) #USER_NAME# [#STAT_GUEST_ID#]
Сессия      - #SESSION_ID#
IP адрес    - #IP#
Время       - #TIME#

Для просмотра данного голосования воспользуйтесь ссылкой:
http://#SERVER_NAME#/bitrix/admin/vote_user_results.php?EVENT_ID=#ID#&lang=ru


Для просмотра результирующей диаграммы опроса воспользуйтесь ссылкой:
http://#SERVER_NAME#/bitrix/admin/vote_results.php?lang=ru&VOTE_ID=#VOTE_ID#

Письмо сгенерировано автоматически.
";
?>