alter database set time_zone='Asia/Ho_Chi_Minh';
shutdown immediate;
startup;


select dbtimezone from dual;