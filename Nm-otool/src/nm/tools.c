/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   tools.c                                            :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/30 17:09:51 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/30 17:09:52 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

void		file_broken(void)
{
	ft_putstr("File is broken...\n");
}

int			verif(void *ask)
{
	if (ask <= g_buff->adr + g_buff->size)
		return (1);
	return (0);
}
