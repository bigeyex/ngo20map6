ALTER TABLE `users` ADD COLUMN `register_month` char(4) AFTER `expertise`, ADD COLUMN `documented_year` char(4) AFTER `register_month`, ADD COLUMN `documented_month` char(4) AFTER `documented_year`, ADD COLUMN `register_type` char(20) AFTER `documented_month`;