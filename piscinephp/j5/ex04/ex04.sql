UPDATE ft_table
SET date_de_creation = ADDDATE(date_de_creation, INTERVAL 20 YEAR)
where id > 5;