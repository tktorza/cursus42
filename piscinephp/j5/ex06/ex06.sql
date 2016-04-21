select titre, resum 
FROM film 
where resum LIKE '%vincent%' COLLATE utf8_general_ci ORDER BY id_film;