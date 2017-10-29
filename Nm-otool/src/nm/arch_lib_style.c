/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   arch_lib_style.c                                   :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/27 12:26:49 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/27 12:26:50 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

t_offlist	*order_off(t_offlist *lst)
{
	t_offlist	*cur;
	uint32_t	tmp;
	int			stop;

	cur = lst;
	stop = 1;
	while (stop)
	{
		stop = 0;
		cur = lst;
		while (cur->next)
		{
			if (cur->off > cur->next->off)
			{
				stop = 1;
				tmp = cur->off;
				cur->off = cur->next->off;
				cur->next->off = tmp;
			}
			cur = cur->next;
		}
	}
	return (lst);
}

t_offlist		*add_off(t_offlist *lst, uint32_t off, uint32_t strx)
{
	t_offlist	*tmp;
	t_offlist	*tmp2;

	tmp = (t_offlist*)malloc(sizeof(t_offlist));
	tmp->off = off;
	tmp->strx = strx;
	tmp->next = NULL;
	if (!lst)
		return (tmp);
	tmp2 = lst;
	while (tmp2->next)
		tmp2 = tmp2->next;
	if (search_lst(lst, off))
		return (lst);
	tmp2->next = tmp;
	return (lst);
}

void		print_ar(t_offlist *lst, char *ptr, char *file,
	t_symtab *symt)
{
	t_offlist		*tmp;
	int				size_name;
	struct ar_hdr	*arch;
	char			*name;

	tmp = lst;
	while (tmp)
	{
		arch = (void*)ptr + tmp->off;
		name = catch_name(arch->ar_name);
		size_name = catch_size(arch->ar_name);
		ft_printf("\n%s(%s):\n", file, name);
		type_bin((void*)arch + sizeof(*arch) + size_name, file, symt,
		symt->bonus);
		tmp = tmp->next;
	}
}

void		handle_lib(char *ptr, char *name, t_symtab *symt)
{
	struct ar_hdr	*arch;
	struct ranlib	*ran;
	t_offlist		*lst;
	char			*offset_struct;

	lst = NULL;
	symt->x = 0;
	arch = (void *)ptr + SARMAG;
	symt->size_name = catch_size(arch->ar_name);
	offset_struct = (void *)ptr + sizeof(*arch) + SARMAG + symt->size_name;
	ran = (void *)ptr + sizeof(*arch) + SARMAG + symt->size_name + 4;
	symt->size = *((int *)offset_struct);
	symt->size = symt->size / sizeof(struct ranlib);
	while (symt->x < symt->size)
	{
		lst = add_off(lst, ran[symt->x].ran_off, ran[symt->x].ran_un.ran_strx);
		symt->x++;
	}
	print_ar(order_off(lst), ptr, name, symt);
}
