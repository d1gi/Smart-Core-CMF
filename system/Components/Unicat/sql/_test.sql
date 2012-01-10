

SELECT i.item_id, i.uri_part, icr.category_id, i.create_datetime, i.owner_id
FROM unicat_items_categories_relation AS icr
LEFT JOIN unicat_items AS i USING (item_id, entity_id, site_id)
WHERE icr.entity_id = '2'
AND icr.site_id = '1'
AND i.is_active = 1 
AND i.is_deleted = 0 
AND ( icr.category_id = '1'  
	OR icr.category_id = '5'  
	OR icr.category_id = '13'  
	OR icr.category_id = '15'  
	OR icr.category_id = '16'  
	OR icr.category_id = '14'  
	OR icr.category_id = '19'  
	OR icr.category_id = '18'  
	OR icr.category_id = '12'  
	OR icr.category_id = '17'
) 
LIMIT 0, 20 

-- Запрос выборки записей в структурах.
SELECT i.item_id, i.uri_part, isr.category_id, i.create_datetime, i.owner_id
FROM unicat_items_structures_relation AS isr
LEFT JOIN unicat_items AS i USING (item_id, entity_id)
WHERE isr.entity_id = '1'
AND i.site_id = '1'
AND i.is_active = 1 
AND i.is_deleted = 0 
AND (
		(isr.structure_id = 1 AND isr.category_id = 2)
		OR  (isr.structure_id = 2 AND isr.category_id = 2)
		OR  (isr.structure_id = 0 AND isr.category_id = 0)
	)
GROUP BY i.item_id
ORDER BY i.item_id ASC