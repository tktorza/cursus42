/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   link_next.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:55:51 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:55:52 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

t_link		*link_next(t_link *link)
{
	t_link	*new;

	new = (t_link *)malloc(sizeof(t_link));
	new->next = (t_link *)malloc(sizeof(t_link));
	link->next = new;
	return (new);
}

t_aff		*aff_next(t_aff *aff)
{
	t_aff	*new;

	if (!aff->final)
		aff->final = 0;
	new = (t_aff *)malloc(sizeof(t_aff));
	new->next = (t_aff *)malloc(sizeof(t_aff));
	aff->next = new;
	new->final = 0;
	return (new);
}

t_bloc		*bloc_next(t_bloc *bloc)
{
	t_bloc	*new;

	new = (t_bloc *)malloc(sizeof(t_bloc));
	new->next = (t_bloc *)malloc(sizeof(t_bloc));
	bloc->next = new;
	return (new);
}

t_file		*file_next(t_file *file)
{
	t_file	*new;

	new = (t_file *)malloc(sizeof(t_file));
	file->next = (t_file *)malloc(sizeof(t_file));
	file->next = new;
	return (new);
}

t_sett		*next_maillon(t_sett *prev)
{
	t_sett	*new;

	new = (t_sett *)malloc(sizeof(t_sett));
	new->next = (t_sett *)malloc(sizeof(t_sett));
	prev->next = new;
	return (new);
}
