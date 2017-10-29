/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   arch_32_style.c                                    :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/27 12:51:28 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/27 12:51:29 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

static char		type_element_32(struct nlist list, t_symtab *symt)
{
	char car;

	car = '?';
	if ((list.n_type & N_TYPE) == N_UNDF)
	{
		if (list.n_value)
			car = 'C';
		else
			car = 'U';
	}
	else if ((list.n_type & N_TYPE) == N_ABS)
		car = 'A';
	else if ((list.n_type & N_TYPE) == N_PBUD)
		car = 'U';
	else if ((list.n_type & N_TYPE) == N_SECT)
		car = type_n_sect(list.n_sect, symt);
	else if ((list.n_type & N_TYPE) == N_INDR)
		car = 'I';
	if (!(list.n_type & N_EXT) && car != '?')
		car = ft_tolower(car);
	return (car);
}

static void		symtab_building_bis_32(t_symtab *symt,
	struct segment_command *seg, struct section *sect)
{
	symt->i = 0;
	while (symt->i < seg->nsects)
	{
		if (ft_strcmp(sect->sectname, SECT_TEXT) == 0 &&
			ft_strcmp(sect->segname, SEG_TEXT) == 0)
		{
			symt->text = symt->ns;
		}
		else if (ft_strcmp(sect->sectname, SECT_DATA) == 0 &&
			ft_strcmp(sect->segname, SEG_DATA) == 0)
		{
			symt->data = symt->ns;
		}
		else if (ft_strcmp(sect->sectname, SECT_BSS) == 0 &&
			ft_strcmp(sect->segname, SEG_DATA) == 0)
			symt->bss = symt->ns;
		sect = (void *)sect + sizeof(*sect);
		symt->ns++;
		symt->i++;
	}
}

void			symtab_building_32(t_symtab *symt, struct mach_header
		*header, struct load_command *lc)
{
	struct segment_command	*seg;
	struct section			*sect;

	while (symt->j < header->ncmds)
	{
		if (lc->cmd == LC_SEGMENT)
		{
			seg = (struct segment_command *)lc;
			sect = (struct section *)((void *)seg + sizeof(*seg));
			symtab_building_bis_32(symt, seg, sect);
		}
		lc = (void *)lc + lc->cmdsize;
		symt->j++;
	}
}

static void		print_output_32(struct symtab_command *sym, char *ptr,
		struct mach_header *header, t_symtab *symt)
{
	struct load_command	*lc;
	char				*stringtable;
	struct nlist		*array;
	uint32_t			i;

	i = 0;
	array = (void *)ptr + sym->symoff;
	stringtable = (void *)ptr + sym->stroff;
	lc = (void *)ptr + sizeof(*header);
	array = symt->bonus == NO_SORT ? array 
	: tri_bulle(stringtable, array, sym->nsyms);
	symtab_building_32(symt, header, lc);
	while (i < sym->nsyms)
	{
		display_out(array[i], stringtable + array[i].n_un.n_strx,
			type_element_32(array[i], symt), symt);
		i++;
	}
}

void			handle_32(char *ptr, t_symtab *symt)
{
	int						ncmds;
	int						i;
	struct mach_header		*header;
	struct load_command		*lc;
	struct symtab_command	*sym;

	header = (struct mach_header *)ptr;
	ncmds = header->ncmds;
	i = 0;
	lc = (void *)ptr + sizeof(*header);
	while (i < ncmds)
	{
		if (lc->cmd == LC_SYMTAB)
		{
			sym = (struct symtab_command *)lc;
			print_output_32(sym, ptr, header, symt);
			break ;
		}
		lc = (void *)lc + lc->cmdsize;
		i++;
	}
}
