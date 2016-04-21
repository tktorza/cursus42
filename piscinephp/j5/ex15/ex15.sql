SELECT REVERSE(SUBSTRING(telephone, 2)) AS 'enohpelet'
FROM distrib
WHERE SUBSTRING(telephone, 1, 2) = '05'; 
