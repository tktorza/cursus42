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
	char		c;

	c = list.n_type;
	if (c & N_STAB)
		return ('-');
	c = c & N_TYPE;
	if (c == N_UNDF && list.n_value != 0)
		c = 'C';
	else if ((c == N_UNDF && list.n_value == 0) || c == N_PBUD)
		c = 'U';
	else if (c == N_ABS)
		c = 'A';
	else if (c == N_SECT)
		c = type_n_sect(list.n_sect, symt);
	else
		c = (c == N_INDR ? 'I' : '?');
	if (!(list.n_type & N_EXT))
		c = ft_tolower(c);
	return (c);
}

static void		symtab_building_bis(t_symtab *symt,
	struct segment_command_64 *seg, struct section_64 *sect)
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
		if (!verif((void *)sect + sizeof(*sect)))
			return (file_broken());
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
		if (!verif((void *)lc + lc->cmdsize))
			return (file_broken());
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
	if (!verif((void *)array) || !verif((void *)stringtable) ||
	!verif((void *)lc))
		return (file_broken());
	array = symt->bonus == NO_SORT ? array
	: tri_bulle_64(stringtable, array, sym->nsyms);
	symtab_building(symt, header, lc);
	while (i < sym->nsyms)
	{
		display_out_64(array[i], stringtable + array[i].n_un.n_strx,
		type_element(array[i], symt), symt);
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
	lc = (void *)ptr + sizeof(*header);
	if (!verif((void *)lc) || !verif((void *)header))
		return (file_broken());
	ncmds = header->ncmds;
	i = 0;
	while (i < ncmds)
	{
		if (lc->cmd == LC_SYMTAB)
		{
			sym = (struct symtab_command *)lc;
			print_output_64(sym, ptr, header, symt);
			break ;
		}
		if (!verif((void *)lc + lc->cmdsize))
			return (file_broken());
		lc = (void *)lc + lc->cmdsize;
		i++;
	}
}
