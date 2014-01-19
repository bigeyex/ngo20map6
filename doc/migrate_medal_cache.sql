update users u set medal_score=(select sum(score) from medal where medal.id in (select medal_id from medalmap where user_id=u.id))

update users u set medals=(select GROUP_CONCAT(code_name SEPARATOR ',') from medal where medal.id in (select medal_id from medalmap where user_id=u.id))
