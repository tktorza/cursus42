/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   arch_64_style.c                                    :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/27 12:26:41 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/27 12:26:42 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

static char		type_element(struct nlist_64 list, t_symtab *symt)
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

static void		symtab_building_bis(t_symtab *symt, struct
	segment_command_64 *seg, struct section_64 *sect)
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

void			symtab_building(t_symtab *symt, struct
	mach_header_64 *header, struct load_command *lc)
{
	struct segment_command_64	*seg;
	struct section_64			*sect;

	while (symt->j < header->ncmds)
	{
		if (lc->cmd == LC_SEGMENT_64)
		{
			seg = (struct segment_command_64 *)lc;
			sect = (struct section_64 *)((void *)seg + sizeof(*seg));
			symtab_building_bis(symt, seg, sect);
		}
		lc = (void *)lc + lc->cmdsize;
		symt->j++;
	}
}

static void		print_output_64(struct symtab_command *sym, char *ptr,
							struct mach_header_64 *header, t_symtab *symt)
{
	struct load_command		*lc;
	char					*stringtable;
	struct nlist_64			*array;
	uint32_t				i;

	i = 0;
	array = (void *)ptr + sym->symoff;
	stringtable = (void *)ptr + sym->stroff;
	lc = (void *)ptr + sizeof(*header);
	array = tri_bulle_64(stringtable, array, sym->nsyms);
	symtab_building(symt, header, lc);
	while (i < sym->nsyms)
	{
		display_out_64(array[i], stringtable + array[i].n_un.n_strx,
		type_element(array[i], symt));
		i++;
	}
}

void			handle_64(char *ptr, t_symtab *symt)
{
	int						ncmds;
	int						i;
	struct mach_header_64	*header;
	struct load_command		*lc;
	struct symtab_command	*sym;

	header = (struct mach_header_64 *)ptr;
	ncmds = header->ncmds;
	i = 0;
	lc = (void *)ptr + sizeof(*header);
	while (i < ncmds)
	{
		if (lc->cmd == LC_SYMTAB)
		{
			sym = (struct symtab_command *)lc;
			print_output_64(sym, ptr, header, symt);
			break ;
		}
		lc = (void *)lc + lc->cmdsize;
		i++;
	}
}
