/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   handle_o.c                                         :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/27 14:04:55 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/27 14:05:26 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

void	handle_o_64(char *ptr, char *file, t_symtab *symt)
{
	int						ncmds;
	int						i;
	struct mach_header_64	*header;
	struct load_command		*lc;

	header = (struct mach_header_64 *)ptr;
	ncmds = header->ncmds;
	i = 0;
	lc = (void *)ptr + sizeof(*header);
	(symt->lib == 1) ? 0 : ft_printf("%s:\n", file);
	symtab_building(symt, header, lc);
}

void	handle_o_32(char *ptr, char *file, t_symtab *symt)
{
	int						ncmds;
	int						i;
	struct mach_header		*header;
	struct load_command		*lc;

	header = (struct mach_header *)ptr;
	ncmds = header->ncmds;
	i = 0;
	lc = (void *)ptr + sizeof(*header);
	(symt->lib == 1) ? 0 : ft_printf("%s:\n", file);
	symtab_building_32(symt, header, lc);
}

void	handle_o_lib(char *ptr, char *name, t_symtab *symt)
{
	struct ar_hdr	*arch;
	struct ranlib	*ran;
	t_offlist		*lst;
	char			*test;

	symt->lib = 1;
	ft_printf("Archive : %s", name);
	symt->x = 0;
	arch = (void*)ptr + SARMAG;
	symt->size_name = catch_size(arch->ar_name);
	test = (void*)ptr + sizeof(*arch) + SARMAG + symt->size_name;
	ran = (void*)ptr + sizeof(*arch) + SARMAG + symt->size_name + 4;
	symt->size = *((int *)test);
	lst = NULL;
	symt->size = symt->size / sizeof(struct ranlib);
	while (symt->x < symt->size)
	{
		lst = add_off(lst, ran[symt->x].ran_off, ran[symt->x].ran_un.ran_strx);
		symt->x++;
	}
	print_ar(order_off(lst), ptr, name, symt);
	write(1, "\n", 1);
}
