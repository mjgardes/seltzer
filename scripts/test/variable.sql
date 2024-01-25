use i3crm;
insert into variable (name, value) values ('environment', 'TEST');
update variable set value = 'test_storage@i3detroit.org' where name = 'storage_admin_email';
insert into variable (name, value) values ('contact_email', 'test_contact@i3detroit.org');