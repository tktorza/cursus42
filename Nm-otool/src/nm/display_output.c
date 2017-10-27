/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   display_output.c                                   :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/27 12:26:53 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/27 12:27:00 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

char	type_n_sect(unsigned int n_sect, t_symtab *symt)
{
	if (n_sect == symt->text)
		return ('T');
	if (n_sect == symt->data)
		return ('D');
	if (n_sect == symt->bss)
		return ('B');
	return ('S');
}

void	display_out_64(struct nlist_64 elem, char *str, char type)
{
	if (ft_strcmp("radr://5614542", str) == 0)
		return ;
	if (elem.n_value == 0 && (type == 'U' || type == 'u'))
	{
		if (ft_strcmp("__mh_execute_header", str) == 0)
			ft_printf("%016llx %c %s\n", elem.n_value, type, str);
		else
			ft_printf("%16c %c %s\n", ' ', type, str);
	}
	else
		ft_printf("%016llx %c %s\n", elem.n_value, type, str);
}

void	display_out(struct nlist elem, char *str, char type)
{
	if (ft_strcmp("radr://5614542", str) == 0)
		return ;
	if (elem.n_value == 0 && (type == 'U' || type == 'u'))
	{
		if (ft_strcmp("__mh_execute_header", str) == 0)
			ft_printf("%08llx %c %s\n", elem.n_value, type, str);
		else
			ft_printf("%8c %c %s\n", ' ', type, str);
	}
	else
		ft_printf("%08llx %c %s\n", elem.n_value, type, str);
}
