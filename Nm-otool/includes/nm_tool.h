/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   nm_tool.h                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/03 12:02:15 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 13:49:03 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#ifndef NM_TOOL_H
# define NM_TOOL_H

# include <fcntl.h>
# include <stdio.h>
# include <sys/mman.h>
# include <mach-o/loader.h>
# include <mach-o/nlist.h>
# include <mach-o/ranlib.h>
# include <mach-o/fat.h>
# include <ar.h>
# include <sys/stat.h>
# include <stdlib.h>
# include "../libft/inc/libft.h"
# include "../libft/inc/ft_printf.h"

#define ERROR(name) ft_printf("ft_nm: %s", name); return (-1)

int g_data;
int g_bss;
int g_text;



typedef struct          s_symtab
{
    uint32_t		data;
    uint32_t		bss;
    uint32_t		text;
    uint32_t		i;
    uint32_t		j;
    uint32_t		ns;
    int				exec;
	int				otool;
	int				x;
	int				size;
	int				size_name;
}                       t_symtab;

typedef struct			s_offlist
{
	uint32_t			off;
	uint32_t			strx;
	struct s_offlist	*next;
}						t_offlist;


int			search_lst(t_offlist *lst, uint32_t off);
struct nlist_64	*fill_array_64(struct nlist_64 *tab, uint32_t taille);
struct nlist	*fill_array(struct nlist *tab, uint32_t taille);

uint32_t   swap_uint32(struct fat_header *fheader, uint32_t val);
int ft_nm(char *av);
int type_bin(char *ptr, char *file, t_symtab *symt);
int main(int ac, char **av);

struct nlist     *tri_bulle(char *stringtable, struct nlist *tab,
    uint32_t taille);
struct nlist_64     *tri_bulle_64(char *stringtable, struct nlist_64 *tab,
    uint32_t taille);
    
    
void handle_32(char *ptr, t_symtab *symt);

void handle_64(char *ptr, t_symtab *symt);


void handle_lib(char *ptr, char *file, t_symtab *symt);
t_offlist	*order_off(t_offlist *lst);
int			catch_size(char *name);
char		*catch_name(char *name);
t_offlist		*add_off(t_offlist *lst, uint32_t off, uint32_t strx);
void		print_ar(t_offlist *lst, char *ptr, char *file, t_symtab *symt);
void			browse_ar(t_offlist *lst, char *ptr, char *name, t_symtab *symt);

void handle_fat(char *ptr, char * file, t_symtab *symt);


void display_out_64(struct nlist_64 elem, char *str, char type);
void display_out(struct nlist elem, char *str, char type);
char type_n_sect(unsigned int n_sect, t_symtab *symt);


void handle_o_32(char *ptr, char *file, t_symtab *symt);
void handle_o_64(char *ptr, char *file, t_symtab *symt);
void	handle_o_lib(char *ptr, char *name, t_symtab *symt);
void    symtab_building_32(t_symtab *symt, struct mach_header *header,\
    struct load_command *lc);
void    symtab_building(t_symtab *symt, struct mach_header_64 *header,\
    struct load_command *lc);

int                 ft_printf(const char *str, ...);

#endif
