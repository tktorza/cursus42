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

char		type_n_sect(unsigned int n_sect, t_symtab *symt)
{
	if (n_sect == symt->text)
		return ('T');
	if (n_sect == symt->data)
		return ('D');
	if (n_sect == symt->bss)
		return ('B');
	return ('S');
}

static void	mask_check_64(struct nlist_64 elem, char *str, char type,
	t_symtab *symt)
{
	if ((symt->bonus & NOT_UNDEFINED) == 0)
	{
		if (ft_strcmp("__mh_execute_header", str) == 0)
		{
			if ((symt->bonus & SYMBOL_NAME) != 0 ||
			(symt->bonus & UNDEFINED) != 0)
				ft_printf("%s\n", str);
			else
			{
				if ((symt->bonus & DECIMAL) != 0)
					ft_printf("%016llu %c %s\n", elem.n_value, type, str);
				else
					ft_printf("%016llx %c %s\n", elem.n_value, type, str);
			}
		}
		else
		{
			if ((symt->bonus & SYMBOL_NAME) != 0 ||
			(symt->bonus & UNDEFINED) != 0)
				ft_printf("%s\n", str);
			else
				ft_printf("%16c %c %s\n", ' ', type, str);
		}
	}
}

void		display_out_64(struct nlist_64 elem, char *str, char type,
	t_symtab *symt)
{
	if (ft_strcmp("radr://5614542", str) == 0 || (type == '-'))
		return ;
	else if (elem.n_value == 0 && (type == 'U' || type == 'u'))
		mask_check_64(elem, str, type, symt);
	else
	{
		if ((symt->bonus & UNDEFINED) != 0)
			return ;
		else
		{
			if ((symt->bonus & SYMBOL_NAME) != 0 ||
			(symt->bonus & UNDEFINED) != 0)
				ft_printf("%s\n", str);
			else if ((symt->bonus & DECIMAL) != 0)
				ft_printf("%016llu %c %s\n", elem.n_value, type, str);
			else
				ft_printf("%016llx %c %s\n", elem.n_value, type, str);
		}
	}
}

static void	mask_check(struct nlist elem, char *str, char type,
	t_symtab *symt)
{
	if ((symt->bonus & NOT_UNDEFINED) == 0)
	{
		if (ft_strcmp("__mh_execute_header", str) == 0)
		{
			if ((symt->bonus & SYMBOL_NAME) != 0 ||
			(symt->bonus & UNDEFINED) != 0)
				ft_printf("%s\n", str);
			else
			{
				if ((symt->bonus & DECIMAL) != 0)
					ft_printf("%08lld %c %s\n", elem.n_value, type, str);
				else
					ft_printf("%08llx %c %s\n", elem.n_value, type, str);
			}
		}
		else
		{
			if ((symt->bonus & SYMBOL_NAME) != 0 ||
			(symt->bonus & UNDEFINED) != 0)
				ft_printf("%s\n", str);
			else
				ft_printf("%8c %c %s\n", ' ', type, str);
		}
	}
}

void		display_out(struct nlist elem, char *str, char type,
	t_symtab *symt)
{
	if (ft_strcmp("radr://5614542", str) == 0 || (type == '-'))
		return ;
	else if (elem.n_value == 0 && (type == 'U' || type == 'u'))
		mask_check(elem, str, type, symt);
	else
	{
		if ((symt->bonus & UNDEFINED) != 0)
			return ;
		else
		{
			if ((symt->bonus & SYMBOL_NAME) != 0)
				ft_printf("%s\n", str);
			else if ((symt->bonus & DECIMAL) != 0)
				ft_printf("%08lld %c %s\n", elem.n_value, type, str);
			else
				ft_printf("%08llx %c %s\n", elem.n_value, type, str);
		}
	}
}
