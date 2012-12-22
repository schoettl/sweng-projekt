use sweng_projekt;

-- key 1 darf bei lock 1, key 2 nicht!
insert into whitelist values (1,1);
insert into blacklist values (1,2);

-- key 2: auf black UND whitelist von lock 2 -> kein zugang
insert into whitelist values (2,2);
insert into blacklist values (2,2);

-- key 1: access fuer lock 2
insert access values (1, 2, 1, NOW(), null);
-- key 3: kein access fuer lock 2 mehr
insert access values (1, 2, 3, NOW(), '2012-12-23 00:37:06');

-- active key 25: access fuer lock 2
insert access values (2, 2, 25, NOW(), null);

select * from access;
