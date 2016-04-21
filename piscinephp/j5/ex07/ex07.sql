SELECT titre, resum 
FROM film 
WHERE (resum LIKE '%42%' || titre LIKE '%42%') ORDER BY duree_min;